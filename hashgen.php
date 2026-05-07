<?php
$pass = 'Admin123!';
echo password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);