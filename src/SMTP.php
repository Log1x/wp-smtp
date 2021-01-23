<?php

namespace Log1x\SMTP;

use function Env\env;

class SMTP
{
    /**
     * Default Config
     *
     * @var array
     */
    protected $config = [
        'auth' => true,
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ],
        'timeout' => 10,
        'host' => '',
        'port' => 587,
        'protocol' => 'tls',
        'username' => '',
        'password' => '',
        'forceFrom' => '',
        'forceFromName' => '',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->admin();
        $this->mail();
    }

    /**
     * Config
     *
     * @return object
     */
    protected function config()
    {
        $config = collect([
            'host' => env('WP_SMTP_HOST'),
            'port' => env('WP_SMTP_PORT'),
            'protocol' => env('WP_SMTP_PROTOCOL'),
            'username' => env('WP_SMTP_USERNAME'),
            'password' => env('WP_SMTP_PASSWORD'),
            'timeout' => env('WP_SMTP_TIMEOUT'),
            'forceFrom' => env('WP_SMTP_FORCEFROM'),
            'forceFromName' => env('WP_SMTP_FORCEFROMNAME')
        ])->filter();

        return (object) collect($this->config)
            ->merge($config)
            ->all();
    }

    /**
     * Validate Hash
     *
     * @return boolean
     */
    protected function validate()
    {
        return $this->hash() === get_option('wp_mail_verify');
    }

    /**
     * Hash
     *
     * @return string
     */
    protected function hash()
    {
        return hash('crc32', serialize($this->config()));
    }

    /**
     * Notice
     *
     * @param  string $message
     * @param  string $type
     * @param  boolean $dismissible
     * @return void
     */
    protected function notice($message, $type = 'info', $dismissible = false)
    {
        add_action('admin_notices', function () use ($message, $type, $dismissible) {
            printf(
                '<div class="%1$s"><p>%2$s</p></div>',
                esc_attr("notice notice-{$type}" . ($dismissible ? ' is-dismissible' : '')),
                __($message, 'wp-smtp')
            );
        });
    }

    /**
     * Admin
     *
     * @return void
     */
    protected function admin()
    {
        if (! empty($_GET['verify-smtp']) && $_GET['verify-smtp'] === 'true' && wp_verify_nonce($_GET['_wpnonce'], 'verify-smtp')) {
            $this->verify();
        }

        if (! $this->config()->username || ! $this->config()->password || ! $this->config()->host) {
            return $this->notice(
                'WP SMTP failed to find your SMTP credentials. Please define them in <code>.env</code> and <a href="'.wp_nonce_url(admin_url(add_query_arg('verify-smtp', 'true', 'index.php')), 'verify-smtp').'">click here</a> to test your configuration.',
                'error'
            );
        }

        if (! get_option('wp_mail_verify')) {
            return $this->notice(
                'WP SMTP credentials found. Please <a href="'.wp_nonce_url(admin_url(add_query_arg('verify-smtp', 'true', 'index.php')), 'verify-smtp').'">click here</a> to test your configuration.'
            );
        }

        if (! $this->validate()) {
            return $this->notice(
                'WP SMTP has detected a change in your credentials. Please <a href="'.wp_nonce_url(admin_url(add_query_arg('verify-smtp', 'true', 'index.php')), 'verify-smtp').'">click here</a> to test your configuration.'
            );
        }
    }

    /**
     * Mail
     *
     * @return void
     */
    protected function mail()
    {
        if ($this->config()->username && $this->config()->password && $this->config()->host) {
            add_action('phpmailer_init', function ($mail) {
                $mail->isSMTP();
                $mail->SMTPAuth = $this->config()->auth;
                $mail->SMTPSecure = $this->config()->protocol;
                $mail->SMTPOptions = ['ssl' => $this->config()->ssl];
                $mail->Timeout = $this->config()->timeout;

                $mail->Host = $this->config()->host;
                $mail->Port = $this->config()->port;
                $mail->Username = $this->config()->username;
                $mail->Password = $this->config()->password;

                $mail->setFrom($mail->From, $mail->FromName);

                if ($this->config()->forceFrom && $this->config()->forceFromName) {
                    $mail->setFrom($this->config()->forceFrom, $this->config()->forceFromName);
                }
            }, PHP_INT_MAX);
        }
    }

    /**
     * Verify SMTP Credentials
     *
     * @return void
     */
    protected function verify()
    {
        require_once(ABSPATH . WPINC . '/class-phpmailer.php');
        $mail = new \PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->SMTPAuth = $this->config()->auth;
            $mail->SMTPOptions = ['ssl' => $this->config()->ssl];
            $mail->SMTPSecure = $this->config()->protocol;
            $mail->SMTPAutoTLS = false;

            $mail->Host = $this->config()->host;
            $mail->Username = $this->config()->username;
            $mail->Password = $this->config()->password;
            $mail->Port = $this->config()->port;
            $mail->Timeout = $this->config()->timeout;

            $mail->setFrom('no-reply@'.preg_replace('/https?:\/\/(www\.)?/', '', get_bloginfo('url')), get_bloginfo('name'));
            $mail->AddAddress(wp_get_current_user()->user_email);

            if ($this->config()->forceFrom && $this->config()->forceFromName) {
                $mail->setFrom($this->config()->forceFrom, $this->config()->forceFromName);
            }

            $mail->CharSet = get_bloginfo('charset');
            $mail->Subject = 'WP SMTP Validation';
            $mail->Body = 'Success.';

            $mail->Send();
            $mail->ClearAddresses();
            $mail->ClearAllRecipients();
        } catch (\phpMailerException $error) {
            return $this->notice(
                $error->errorMessage(),
                'error',
                true
            );
        } catch (Exception $error) {
            return $this->notice(
                $error->getMessage(),
                'error',
                true
            );
        }

        if (update_option('wp_mail_verify', $this->hash())) {
            return $this->notice(
                'WP SMTP connection successful!',
                'success',
                true
            );
        }
    }
}

if (function_exists('add_action')) {
    return new SMTP;
}
