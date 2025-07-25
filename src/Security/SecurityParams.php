<?php

namespace App\Security;

class SecurityParams
{

    const SESSION_KEY_NAME = 'app_unlocked';

    const CODE_TRIES = 'tries';

    const LOCK_TRY_UNTIL = 'lock_try_until';

    const CODE_SUBMIT_BLOCKING_DELAY = '+10 minutes';

    const MAX_TRIES = 3;

    const MAX_SESSION_TIME = 10 * 60; // 10 minutes en secondes

}
