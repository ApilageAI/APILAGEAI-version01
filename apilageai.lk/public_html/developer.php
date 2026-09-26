<?php
require_once __DIR__ . '/../backend/bootstrap.php';

$smarty->assign('is_logged_in', $user->_logged_in);

page_header(
    'ApilageAI Developers | API Keys, Usage, Pricing, Playground, and Documentation',
    'Official ApilageAI developer portal for API keys, usage analytics, pricing, rate limits, playground testing, and Sri Lanka-focused AI API documentation.'
);

page_footer('developer');
?>
