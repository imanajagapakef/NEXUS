<?php

return [
    'tenant' => [
        'session_key' => env('NEXUS_TENANT_SESSION_KEY', 'nexus_selected_org_id'),
        'header' => env('NEXUS_TENANT_HEADER', 'X-Organization-Id'),
    ],
    'audit' => [
        'enabled' => env('NEXUS_AUDIT_ENABLED', true),
        'actions' => [
            'create_expense' => 'CREATE_EXPENSE',
            'review_expense' => 'REVIEW_EXPENSE',
            'approve_expense' => 'APPROVE_EXPENSE',
            'complete_expense' => 'COMPLETE_EXPENSE',
            'reject_expense' => 'REJECT_EXPENSE',
        ],
    ],
    'expense' => [
        'permissions' => [
            'create' => 'expense.create',
            'read' => 'expense.read',
            'update' => 'expense.update',
            'delete' => 'expense.delete',
            'submit' => 'expense.submit',
            'review' => 'expense.review',
            'approve' => 'expense.approve',
            'reject' => 'expense.reject',
            'complete' => 'expense.complete',
        ],
    ],
];
