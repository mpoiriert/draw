DrawPostOfficeBundle
====================

**Be since the Symfony/Mailer is not completed yet some behavior may be affected in later release, consider this
bundle as experimental too**

This bundle allows to delegate creation of email to a specific class.

It also allows configuration for a default **from**.

## Configuration

```
draw_post_office:
  default_from: 'support@example.com'
```

Instead of building your email in your controller directly you create a class that
extend from the **Symfony\Component\Mime\Email** and create a **writer** for it.

Any service that implement the **Draw\Component\Mailer\EmailWriter\EmailWriterInterface**
will be registered as a writer. The **getForEmails** must return a map of method with priority as the value
to register method as a writer (if you return the method as the value it will consider is priority to be 0). 
The system will detect if the email match the class of the first argument of the method and call it if needed.

The Post Office declare a listener for **Symfony\Component\Mailer\Event\MessageEvent** to hook it to the
symfony mailer.

By convention, it's recommend to create an **Email** folder in which you will create all your email class
and also a **EmailWriter** for your class that does implement the **Draw\Component\Mailer\EmailWriter\EmailWriterInterface**.

## Example

Let's create a forgot password email, this class will contain the **minimum** information to compose the email,
in that case the email of the user that trigger the forgot password email flow.

```PHP
<?php namespace App\Email;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ForgotPasswordEmail extends TemplatedEmail
{
    private $emailAddress;

    public function __construct(string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
        parent::__construct();
    }

    /**
     * The email address of the person who forgot is email
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }
}
```

We must create a **writer** for the email:

```PHP
<?php namespace App\Email;

use App\Email\ForgotPasswordEmail;
use App\LostPasswordTokenProvider;
use Draw\Component\Mailer\Email\EmailWriterInterface;

class ForgotPasswordEmailWriter implements EmailWriterInterface
{
    private $lostPasswordTokenProvider;
    
    public function __construct(LostPasswordTokenProvider $lostPasswordTokenProvider)
    {
        $this->lostPasswordTokenProvider = $lostPasswordTokenProvider;
    }
    
     public static function getForEmails(): array
     {
         return ['compose']; // Or ['compose' => 0];
         
     }
    
    public function compose(ForgotPasswordEmail $forgotPasswordEmail)
    {
        $emailAddress = $forgotPasswordEmail->getEmailAddress();
        $forgotPasswordEmail
            ->to($emailAddress)
            ->subject('You have forgotten your password !')
            ->htmlTemplate('emails/forgot_password.html.twig')
            ->context([
                'token' => $this->lostPasswordTokenProvider->generateToken($emailAddress)
            ]);
    }
}
```

The basic controller example:

```PHP
<?php namespace App\Controller;

use App\Email\ForgotPasswordEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ForgotPasswordController
{
    public function forgotPasswordAction(
        Request $request,
        MailerInterface $mailer
    ): Response {
        if ($request->getMethod() == Request::METHOD_GET) {
            return $this->render('users/forgot_password.html.twig');
        }

        // ... You should have a logic to validate there is a user and send a different email ... /
        $mailer->send(new ForgotPasswordEmail($request->request->get('email')));

        return new RedirectResponse($this->generateUrl('check_email'));
    }
}
```

That way you keep your controller clean and structure how email should be written and overridden. 

The system also pass the **Envelope** parameter as the second argument in case you need it.

If you look at the **Draw\Component\Mailer\EmailWriter\DefaultFromEmailWriter** you will see how to create a writer
that is call for all the email that are sent.