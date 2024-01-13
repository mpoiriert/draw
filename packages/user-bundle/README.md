DrawUserBundle
==============

## Enable 2FA for admin
1. Install and configure scheb/2fa-bundle
```
composer ruquire scheb/2fa-bundle scheb/2fa-totp scheb/2fa-qr-code
```
config/packages/scheb_2fa.yaml
```yaml
scheb_two_factor:
    totp:
        enabled: true
        server_name: draw.example.com
        issuer: Draw
        template: '@DrawUser/security/2fa_login.html.twig'
    security_tokens:
        - Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken
        - Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken
```
config/routes/scheb_2fa.yaml
```yaml
admin_2fa_login:
    path: /admin/2fa
    defaults:
        _controller: "scheb_two_factor.form_controller::form"

admin_2fa_login_check:
    path: /admin/2fa_check
```
2. Enable two-factor authentication per firewall and configure access_control for the 2fa routes:
config/packages/security.yaml
```yaml
security:
    firewalls:
        admin:
            two_factor:
                provider: app_user_provider_email # If you have more than one user provider
                auth_form_path: admin_2fa_login
                check_path: admin_2fa_login_check

      access_control:
        - { path: ^/admin/2fa, role: IS_AUTHENTICATED_2FA_IN_PROGRESS }
        - { path: ^/admin/logout$, role: IS_AUTHENTICATED_ANONYMOUSLY }
```
3. Implements Draw\Bundle\UserBundle\Security\TwoFactorAuthenticationUserInterface and 
use \Draw\Bundle\UserBundle\Entity\TwoFactorAuthenticationUserTrait for User entity.
Migrate database changes.
4. Enable 2FA in DrawUserBundle.
```yaml
draw_user:
    sonata:
        enabled: true
        user_admin_code: App\Sonata\Admin\UserAdmin
        2fa:
            enabled: true
            field_positions:
                2fa_enabled:
                    #Those are the default
                    list: '_action' #Before the _action list. Dynamically set to _actions for sonata 4.x
                    form: true #at the end of the form
```

Two actions will be available when 2fa is enabled: 2fa-enable and 2fa-disable.
The access right are configure via the TwoFactorAuthenticationExtension::getAccessMapping.
You can override this by overriding the getAccess method of your UserAdmin class.