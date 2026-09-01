<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Support\PermissionResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['email'=>'required|email','password'=>'required']);
        $user = \App\Models\User::where('email',$request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message'=>'auth.invalid_credentials','retry'=>true],422);
        }
        Auth::login($user);
        $request->session()->regenerate();
        $memberships = Membership::where('user_id',$user->id)->with('organization','role')->get();
        if ($memberships->isEmpty()) {
            return response()->json(['message'=>'auth.no_organization'],422);
        }
        return response()->json(['user'=>$user,'memberships'=>$memberships]);
    }

    public function selectOrganization(Request $request)
    {
        $request->validate(['organization_id'=>'required|uuid']);
        $user = $request->user();
        $membership = Membership::where('user_id',$user->id)->where('organization_id',$request->organization_id)->first();
        if (!$membership) return response()->json(['message'=>'auth.no_organization'],422);
        if ($membership->status === 'invited') return response()->json(['message'=>'auth.inactive_membership'],403);
        if ($membership->status !== 'active') return response()->json(['message'=>'auth.inactive_membership'],403);
        $membership->load('role.permissions');
        $perms = PermissionResolver::for($membership);
        if (empty($perms)) return response()->json(['message'=>'auth.permission_denied'],403);
        $request->session()->put(config('nexus.tenant.session_key'), $membership->organization_id);
        return response()->json(['organization_id'=>$membership->organization_id,'permissions'=>$perms]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message'=>'logged out']);
    }
}
