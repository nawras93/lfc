<?php

return [
    'account_type' => [
        'parent' => 'Parent',
        'vvip_client' => 'VVIP Client',
    ],
    'candidate_document_status' => [
        'pending' => 'Pending',
        'received' => 'Received',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],
    'document_status' => [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'complete' => 'Complete',
    ],
    'federation_status' => [
        'not_started' => 'Not Started',
        'submitted' => 'Submitted',
        'approved' => 'Approved',
        'returned' => 'Returned',
    ],
    'fixture_status' => [
        'scheduled' => 'Scheduled',
        'open_for_scanning' => 'Open for Scanning',
        'closed' => 'Closed',
    ],
    'joining_status' => [
        'not_started' => 'Not Started',
        'ready_to_join' => 'Ready To Join',
        'joined_team' => 'Joined Team',
    ],
    'offer_audience' => [
        'all' => 'All Parents',
        'vvip' => 'VVIP Only',
    ],
    'playing_position' => [
        'goalkeeper' => 'Goalkeeper',
        'defender' => 'Defender',
        'midfielder' => 'Midfielder',
        'attacker' => 'Attacker',
    ],
    'point_rule_type' => [
        'fixed' => 'Fixed',
        'percentage' => 'Percentage',
    ],
    'point_transaction_type' => [
        'earn' => 'Earn',
        'redeem' => 'Redeem',
        'expire' => 'Expire',
        'adjust' => 'Adjust',
        'reverse' => 'Reverse',
    ],
    'recruitment_stage' => [
        'new_application' => 'New Application',
        'assessment_scheduled' => 'Assessment Scheduled',
        'assessment_completed' => 'Assessment Completed',
        'accepted' => 'Accepted',
        'waiting_list' => 'Waiting List',
        'rejected' => 'Rejected',
    ],
    'redemption_status' => [
        'issued' => 'Issued',
        'fulfilled' => 'Fulfilled',
        'cancelled' => 'Cancelled',
    ],
    'redemption_type' => [
        'fee' => 'Fee',
        'event' => 'Event',
        'merch' => 'Merchandise',
    ],
    'progress' => [
        'joined' => 'Joined',
        'documents_required' => 'Documents Required',
        'accepted' => 'Accepted',
        'in_progress' => 'In Progress',
    ],
];
