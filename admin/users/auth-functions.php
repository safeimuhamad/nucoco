<?php

function user_auth_token(): string
{
    return bin2hex(random_bytes(32));
}

function user_auth_token_hash(string $token): string
{
    return hash('sha256', $token);
}

function user_auth_email_shell(string $title, string $body, string $buttonLabel, string $buttonUrl): string
{
    return '
        <div style="font-family:Arial,sans-serif;background:#f4f8f3;padding:28px;color:#122033;">
            <div style="max-width:560px;margin:auto;background:#fff;border-radius:10px;padding:28px;border:1px solid #dfe8dc;">
                <h2 style="margin:0 0 14px;color:#08751f;">' . htmlspecialchars($title) . '</h2>
                <div style="font-size:15px;line-height:1.7;color:#344054;">' . $body . '</div>
                <p style="margin:26px 0;">
                    <a href="' . htmlspecialchars($buttonUrl) . '" style="background:#08751f;color:#fff;text-decoration:none;padding:13px 20px;border-radius:7px;font-weight:bold;display:inline-block;">' . htmlspecialchars($buttonLabel) . '</a>
                </p>
                <p style="font-size:12px;color:#667085;line-height:1.6;">If the button does not work, open this link:<br>' . htmlspecialchars($buttonUrl) . '</p>
            </div>
        </div>
    ';
}

function send_user_invitation_email(array $user, string $token): bool
{
    $url = admin_url('activate-account.php?token=' . urlencode($token));
    $body = '<p>Hello ' . htmlspecialchars($user['name']) . ',</p>
        <p>You have been invited to access the Nucoco admin system. Please activate your account and create your password using the button below.</p>
        <p>This invitation link will expire in 24 hours.</p>';

    return nucoco_send_mail(
        $user['email'],
        $user['name'],
        'Activate Your Nucoco Admin Account',
        user_auth_email_shell('Activate Your Account', $body, 'Activate Account', $url)
    );
}

function send_password_reset_email(array $user, string $token): bool
{
    $url = admin_url('reset-password.php?token=' . urlencode($token));
    $body = '<p>Hello ' . htmlspecialchars($user['name']) . ',</p>
        <p>We received a request to reset your Nucoco admin password. Use the button below to create a new password.</p>
        <p>This reset link will expire in 2 hours.</p>';

    return nucoco_send_mail(
        $user['email'],
        $user['name'],
        'Reset Your Nucoco Admin Password',
        user_auth_email_shell('Reset Your Password', $body, 'Reset Password', $url)
    );
}
