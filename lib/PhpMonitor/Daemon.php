<?php

namespace PhpMonitor;


class Daemon extends \Core_Daemon
{
    protected $loop_interval = 60;
    protected $checks = array();

    protected function setup()
    {
        $this->loop_interval = Config::get('interval');
        $this->checks = Config::getChecks();

        foreach($this->checks as $key => $value)
        {
            $this->checks[$key]['failures'] = 0;
            $this->checks[$key]['status'] = 'unknown';
        }
    }

    protected function execute()
    {
        foreach($this->checks as $checkname => $data)
        {
            $this->runcheck($checkname, $data['url']);
        }
    }

    protected function log_file()
    {
        return '/var/log/phpmonitor.log';
    }

    protected function runcheck($checkname, $url)
    {
        $check = $this->getCheckForUrl($url);

        if(($result = $check->execute($url)) === false)
        {
            if($this->checks[$checkname]['status'] != 'down')
                $this->checks[$checkname]['status'] = 'pending';
            $this->checks[$checkname]['failures']++;

            if($this->checks[$checkname]['status'] == 'pending' && $this->checks[$checkname]['failures'] > Config::get('maxfailures', 3))
            {
                $this->checks[$checkname]['status'] = 'down';
                $this->sendFailureNotification($checkname);
            }

            $this->log(sprintf('check %s (%s) failed (failures=%d)', $checkname, $url, $this->checks[$checkname]['failures']));
        }
        else
        {
            if($this->checks[$checkname]['status'] == 'down')
                $this->sendRestoredNotification($checkname);

            $this->checks[$checkname]['status'] = 'up';
            $this->checks[$checkname]['failures'] = 0;
            $this->log(sprintf('check %s (%s) success (%d ms)', $checkname, $url, $check->getTime()));
        }
    }

    protected function getCheckForUrl($url)
    {
        $protocol = explode('://', $url)[0];

        switch($protocol)
        {
            case 'ping':
                return new \PhpMonitor\Checks\Ping();
            case 'ping6':
                return new \PhpMonitor\Checks\Ping6();
            case 'snmp':
                return new \PhpMonitor\Checks\Snmp();
            case 'http':
            case 'https':
                return new \PhpMonitor\Checks\Http();
            break;
            default:
                trigger_error('Unknown protocol '.$protocol.'. No check implementation found.', E_USER_ERROR);
            break;
        }

        return null;
    }

    protected function sendFailureNotification($checkname)
    {
        $this->log('Send failure notification for check '.$checkname);

	$mail = new \PHPMailer;
	$mail->isSMTP();
	$mail->Host = Config::get('smtp.host');
	$mail->Port = Config::get('smtp.port');
	$mail->SMTPAuth = true;
	$mail->Username = Config::get('smtp.username');
	$mail->Password = Config::get('smtp.password');
	$mail->SMTPSecure = 'tls';
	$mail->XMailer = ' ';
	$mail->isHTML(false);
	$mail->CharSet = 'UTF-8';

	foreach($mail->parseAddresses(Config::get('mail.from')) as $addr)
            $mail->setFrom($addr['address'], $addr['name']);

	foreach($mail->parseAddresses(Config::get('mail.to')) as $addr)
            $mail->addAddress($addr['address'], $addr['name']);

	$mail->Subject = 'Check '.$checkname.' failed';
	$mail->Body = sprintf("Check: %s\nURL: %s\nDate: %s\nStatus: %s:\nFailures: %s\n",
            $checkname, $this->checks[$checkname]['url'], date(DATE_RFC850),
            $this->checks[$checkname]['status'], $this->checks[$checkname]['failures']);

        return $mail->send();
    }

    protected function sendRestoredNotification($checkname)
    {
        $this->log('Send restore notification for check '.$checkname);

	$mail = new \PHPMailer;
	$mail->isSMTP();
	$mail->Host = Config::get('smtp.host');
	$mail->Port = Config::get('smtp.port');
	$mail->SMTPAuth = true;
	$mail->Username = Config::get('smtp.username');
	$mail->Password = Config::get('smtp.password');
	$mail->SMTPSecure = 'tls';
	$mail->XMailer = ' ';
	$mail->isHTML(false);
	$mail->CharSet = 'UTF-8';

	foreach($mail->parseAddresses(Config::get('mail.from')) as $addr)
            $mail->setFrom($addr['address'], $addr['name']);

	foreach($mail->parseAddresses(Config::get('mail.to')) as $addr)
            $mail->addAddress($addr['address'], $addr['name']);

	$mail->Subject = 'Restored check '.$checkname;
	$mail->Body = sprintf("Check: %s\nURL: %s\nDate: %s\nStatus: %s:\n",
            $checkname, $this->checks[$checkname]['url'], date(DATE_RFC850),
            $this->checks[$checkname]['status']);

        return $mail->send();
    }
}

