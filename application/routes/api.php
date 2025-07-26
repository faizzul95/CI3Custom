<?php

/**
 * API Routes
 *
 * This routes only will be available under AJAX requests. This is ideal to build APIs.
 */

require __DIR__ . '/API/v1/authentication.php';
require __DIR__ . '/API/v1/navigation.php';
require __DIR__ . '/API/v1/profile.php';
require __DIR__ . '/API/v1/roles.php';
require __DIR__ . '/API/v1/user.php';
require __DIR__ . '/API/v1/queue.php';