<?php

declare(strict_types=1);
/**
 * @author Nicolas CARPi <nico-git@deltablot.email>
 * @copyright 2023 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */

namespace Elabftw\Services;

use Elabftw\Enums\Action;
use Elabftw\Enums\EmailTarget;
use Elabftw\Exceptions\ImproperActionException;
use Elabftw\Models\Config;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailTest extends \PHPUnit\Framework\TestCase
{
    private Email $Email;

    private Logger $Logger;

    protected function setUp(): void
    {
        $this->Logger = new Logger('elabftw');
        // use NullHandler because we don't care about logs here
        $this->Logger->pushHandler(new NullHandler());
        $MockMailer = $this->createMock(MailerInterface::class);
        $this->Email = new Email($MockMailer, $this->Logger, 'toto@yopmail.com');
    }

    public function testTestemailSend(): void
    {
        $this->assertTrue($this->Email->testemailSend('toto@example.com'));
    }

    public function testNotConfigured(): void
    {
        $MockMailer = $this->createMock(MailerInterface::class);
        $NotConfiguredEmail = new Email($MockMailer, $this->Logger, 'notconfigured@example.com');
        $this->assertFalse($NotConfiguredEmail->testemailSend('toto@example.com'));
    }

    public function testTransportException(): void
    {
        $MockMailer = $this->createMock(MailerInterface::class);
        $MockMailer->method('send')->willThrowException(new TransportException());
        $Email = new Email($MockMailer, $this->Logger, 'yep@nope.blah');
        $this->expectException(ImproperActionException::class);
        $Email->testemailSend('toto@example.com');

    }

    public function testMassEmail(): void
    {
        // count the actual number of items in db (for users & admins), so that the test can be automated
        $activeUsersEmails = $this->Email->getAllEmailAddresses(EmailTarget::ActiveUsers);
        $adminsEmails = $this->Email->getAllEmailAddresses(EmailTarget::Admins);
        $sysAdminsEmails = $this->Email->getAllEmailAddresses(EmailTarget::Sysadmins);

        // count admins of teams
        $TeamsHelper = new TeamsHelper(1);
        $adminsIds = $TeamsHelper->getAllAdminsUserid();

        $replyTo = new Address('sender@example.com', 'Sergent Garcia');
        // Note that non-validated users are not active users
        $this->assertEquals(count($activeUsersEmails), $this->Email->massEmail(EmailTarget::ActiveUsers, null, '', 'yep', $replyTo));
        $this->assertEquals(5, $this->Email->massEmail(EmailTarget::Team, 1, 'Important message', 'yep', $replyTo));
        $this->assertEquals(0, $this->Email->massEmail(EmailTarget::TeamGroup, 1, 'Important message', 'yep', $replyTo));
        $this->assertEquals(count($adminsEmails), $this->Email->massEmail(EmailTarget::Admins, null, 'Important message to admins', 'yep', $replyTo));
        $this->assertEquals(count($sysAdminsEmails), $this->Email->massEmail(EmailTarget::Sysadmins, null, 'Important message to sysadmins', 'yep', $replyTo));
        $this->assertEquals(0, $this->Email->massEmail(EmailTarget::BookableItem, 1, 'Oops', 'My cells died', $replyTo));
        $this->assertEquals(count($adminsIds), $this->Email->massEmail(EmailTarget::AdminsOfTeam, 1, 'Important message to admins of a team', 'yep', $replyTo));
    }

    public function testSendMassEmail():void {
        $Config = Config::getConfig();
        $Config->patch(Action::Update, array('mass_email_in_sequences' => '1'));

        $activeUsersEmails = $this->Email->getAllEmailAddresses(EmailTarget::ActiveUsers);

        $this->assertEquals(
            count($activeUsersEmails),
            $this->Email->sendMassEmail(
                target: EmailTarget::ActiveUsers,
                targetId: null,
                subject: 'elab unit testing',
                body: 'for the win',
                replyTo: new Address('a@a.fr','no name')
            )
        );
    }

    public function testSendEmail(): void
    {
        $this->assertTrue($this->Email->sendEmail(new Address('a@a.fr', 'blah'), 's', 'b'));
    }

    public function testNotifySysadminsTsBalance(): void
    {
        $this->assertTrue($this->Email->notifySysadminsTsBalance(12));
    }

    protected function tearDown(): void
    {
        $Config = Config::getConfig();
        $Config->patch(Action::Update, array('mass_email_in_sequences' => '0'));
    }
}
