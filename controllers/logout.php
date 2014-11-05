<?php

UserSession::endSession();

header('Location: ' . SERVER_URL);