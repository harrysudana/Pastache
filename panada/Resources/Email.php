<?php
/**
 * Panada email API.
 *
 * @package	Resources
 * @link	http://panadaframework.com/
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 * @since	Version 0.1
 */
namespace Resources;

class Email {
    
    public
        /**
        * @var array   Define the reception array variable.
        */
        $rcptTo = array(),
        /**
        * @var string  Define email subject.
        */
        $subject = '',
        /**
        * @var string  Define email content.
        */
        $message = '',
        /**
        * @var string  Define email content type; plan or html.
        */
        $messageType = 'plain',
        /**
        * @var string  Define sender's email.
        */
        $fromEmail = '',
        /**
        * @var string  The sender name.
        */
        $fromName = '',
        /**
        * @var string  Mail application option. The option is: native (PHP mail function) or smtp.
        */
        $mailerType = 'native',
        /**
        * @var integer 1 = High, 3 = Normal, 5 = low.
        */
        $priority = 3,
        /**
        * @var string  SMTP server host.
        */
        $smtpHost = '',
        /**
        * @var integer SMTP server port.
        */
        $smtpPort = 25,
        /**
        * @var string | bool SMTP secure type.
        */
        $smtpSecure = false,
        /**
        * @var string  SMTP username.
        */
        $smtpUsername = '',
        /**
        * @var string  SMTP password.
        */
        $smtpPassword = '',
        /**
        * @var string  String to say "helo/ehlo" to smtp server.
        */
        $smtpEhloHost = 'localhost';
        
    
    private
        /**
        * @var string  Var for saving user email(s) that just converted from $rcptTo array.
        */
        $rcptToCtring = '',
        /**
         * @var integer Define SMTP connection.
         */
        $smtpConnection = 0,
        /**
         * @var integer The SMTP connection timeout, in seconds.
         */
        $timeoutConnection = 30,
        /**
         * @var string  Enter character.
         */
        $breakLine = "\r\n",
        /**
         * @var array Group of debug messages.
         */
        $debugMessages = array(),
        /**
         * @var string  Mailer useragent.
         */
        $panadaXMailer = 'Panada Mailer Version 0.3';
    
    
    /**
     * Main Panada method to send the email.
     *
     * @param string | array
     * @param string
     * @param string
     * @param string
     * @param string
     * @return boolean
     */
    public function mail($rcptTo = '', $subject = '', $message = '', $fromEmail = '', $fromName = ''){
        
        if( is_array($rcptTo) ) {
            $this->rcptTo  = $this->cleanEmail($rcptTo);
        }
        else {
            
            $rcpt_break = explode(',', $rcptTo);
            
            if( count($rcpt_break) > 0 )
                $this->rcptTo  = $this->cleanEmail($rcpt_break);
            else
                $this->rcptTo  = $this->cleanEmail(array($rcptTo));
        }
        
        $this->subject          = $subject;
        $this->message          = $message;
        $this->fromEmail       = $fromEmail;
        $this->fromName        = $fromName;
        $this->rcptToCtring   = implode(', ', $this->rcptTo);
        
        if($this->smtpHost != '' || $this->mailerType == 'smtp') {
            
            $this->mailerType = 'smtp';
            return $this->smtpSend();
        }
        else {
            return $this->mailerNative();
        }
    }
    
    /**
     * Print the debug messages.
     *
     * @return string
     */
    public function printDebug(){
        
        foreach($this->debugMessages as $message)
            echo $message.'<br />';
    }
    
    /**
     *  Make the email address string lower and unspace.
     *
     * @param string
     * @return array
     */
    private function cleanEmail($email){
        
        foreach($email as $email)
            $return[] = trim(strtolower($email));
        
        return $return;
    }
    
    /**
     * Built in mail function from PHP. This is the default function to send the email.
     *
     * @return booelan
     */
    private function mailerNative(){
        
        if( ! mail($this->rcptToCtring, $this->subject, $this->message, $this->header()) ) {
            $this->debugMessages[] = 'Error: Sending email failed';
            return false;
        }
        else {
            $this->debugMessages[] = 'Success: Sending email succeeded';
            return true;
        }
    }
    
    /**
     * Socket write command function.
     *
     * @param string
     * @return void
     */
    private function writeCommand($command){
        
        fwrite($this->smtpConnection, $command);
    }
    
    /**
     * Get string from smtp respnse.
     *
     * @return string
     */
    private function getSmtpResponse() {
        
        $return = '';
        
        while($str = fgets($this->smtpConnection, 515)) {
            
            $this->debugMessages[] = 'Success: ' . $str;
            
            $return .= $str;
            
            //Stop the loop if we found space in 4th character.
            if(substr($str,3,1) == ' ')
                break;
        }
        
        return $return;
    }
    
    /**
     * Open connection to smtp server.
     *
     * @return boolean
     */
    private function smtpConnect() {
        
        //Connect to smtp server
        $this->smtpConnection = fsockopen(
                                    ($this->smtpSecure && $this->smtpSecure == 'ssl' ? 'ssl://' : '').$this->smtpHost,
                                    $this->smtpPort,
                                    $errno,
                                    $errstr,
                                    $this->timeoutConnection
                                );
       
        if( empty($this->smtpConnection) ) {
            
            $this->debugMessages[] = 'Error: Failed to connect to server! Error number: ' .$errno . ' (' . $errstr . ')';
            
            return false;
        }
        
        //Add extra time to get respnose from server.
        socket_set_timeout($this->smtpConnection, $this->timeoutConnection, 0);
        
        $response = $this->getSmtpResponse();
        $this->debugMessages[] = 'Success: ' . $response;
        
        return true;
    }
    
    /**
     * Do login to smtp server.
     *
     * @return boolean
     */
    private function smtpLogin() {
        
        //SMTP authentication command
        $this->writeCommand('AUTH LOGIN' . $this->breakLine);
        
        $response = $this->getSmtpResponse();
        $code = substr($response, 0, 3);
        
        if($code != 334) {
            
            $this->debugMessages[] = 'Error: AUTH not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        // Send encoded username
        $this->writeCommand( base64_encode($this->smtpUsername) . $this->breakLine );
        
        $response = $this->getSmtpResponse();
        $code = substr($response, 0, 3);
        
        if($code != 334){
            
            $this->debugMessages[] = 'Error: Username not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        // Send encoded password
        $this->writeCommand( base64_encode($this->smtpPassword) . $this->breakLine );
        
        $response = $this->getSmtpResponse();
        $code = substr($response, 0, 3);
        
        if($code != 235) {
            
            $this->debugMessages[] = 'Error: Password not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
           
            return false;
        }
        
        return true;
    }
    
    /**
     * Close smtp connection.
     *
     * @return void
     */
    private function smtpClose() {
        
        if( ! empty($this->smtpConnection) ) {
            fclose($this->smtpConnection);
            $this->smtpConnection = 0;
        }
    }
    
    /**
     * Initate the smtp ehlo function.
     *
     * @return boolean
     */
    private function makeEhlo() {  
        
        /**
         * IF smtp not accpeted EHLO then try HELO.
         */
        if( ! $this->smtpEhlo('EHLO') )
            if( ! $this->smtpEhlo('HELO') )
                return false;
        
        return true;
    }
    
    /**
     * Say ehlo to smtp server.
     *
     * @param string
     * @return boolean
     */
    private function smtpEhlo($hello) {
        
        $this->writeCommand( $hello . ' ' . $this->smtpEhloHost . $this->breakLine);
        
        $response = $this->getSmtpResponse();
        $code = substr($response, 0, 3);
        
        $this->debugMessages[] = 'Success: helo reply from server is: ' . $response;
        
        if($code != 250){
            
            $this->debugMessages[] = 'Error: '.$hello.' not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        return true;
    }
    
    /**
     * This is email from method.
     *
     * @return boolean
     */
    private function smtpFrom() {
        
        $this->writeCommand("MAIL FROM:<" . $this->fromEmail . ">" . $this->breakLine);
        
        $response = $this->getSmtpResponse();
        $code = substr($response, 0, 3);
        
        $this->debugMessages[] = 'Success: ' . $response;
        
        if($code != 250) {
            
            $this->debugMessages[] = 'Error: MAIL not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Email to method.
     *
     * @param string
     * @return boolean
     */
    private function smtpRecipient($to) {
        
        $this->writeCommand("RCPT TO:<" . $to . ">" . $this->breakLine);
        
        $response = $this->getSmtpResponse();
        $code = substr($response, 0, 3);
        
        $this->debugMessages[] = 'Success: ' . $response;
        
        if($code != 250 && $code != 251) {
            
            $this->debugMessages[] = 'Error: RCPT not accepted from server! Error number: ' .$code . ' (' . substr($response,4) . ')';
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Create the email header.
     *
     * @return string
     */
    private function header(){
        
        $fromName  = ($this->fromName != '') ? $this->fromName : $this->fromEmail;
        
        $headers['from']        = 'From: ' . $fromName . ' <' . $this->fromEmail . '>' . $this->breakLine;
        $headers['priority']    = 'X-Priority: '. $this->priority . $this->breakLine;
        $headers['mailer']      = 'X-Mailer: ' .$this->panadaXMailer . $this->breakLine;
        $headers['mime']        = 'MIME-Version: 1.0' . $this->breakLine;
        $headers['cont_type']   = 'Content-type: text/'.$this->messageType.'; charset=iso-8859-1' . $this->breakLine;
        
        if($this->mailerType == 'native') {
            $return = '';
            foreach($headers as $headers)
                $return .= $headers;
            
            return $return;
        }
        else {
            
            // Additional headers needed by smtp.
            $this->writeCommand('To: ' . $this->rcptToCtring . $this->breakLine);
            $this->writeCommand('Subject:' . $this->subject. $this->breakLine);
            
            foreach($headers as $key => $val) {
                
                if($key == 'cont_type')
                    $val = str_replace($this->breakLine, "\n\n", $val);
                
                $this->writeCommand($val);
            }
        }
    }
    
    /**
     * Send the mail data.
     *
     * @return boolean
     */
    private function smtpData() {
        
        $this->writeCommand('DATA' . $this->breakLine);
        
        $response = $this->getSmtpResponse();
        $code = substr($response, 0, 3);
        
        $this->debugMessages[] = 'Success: ' . $response;
        
        if($code != 354) {
            
            $this->debugMessages[] = 'Error: DATA command not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        $this->header();
        $this->writeCommand($this->message . $this->breakLine);
        
        
        //All messages have sent
        $this->writeCommand( $this->breakLine . '.' . $this->breakLine);
        
        $response = $this->getSmtpResponse();
        $code = substr($response, 0, 3);
        
        $this->debugMessages[] = 'Success: ' . $response;
        
        if($code != 250){
            
            $this->debugMessages[] = 'Error: DATA command not accepted from server! Error number: ' .$code . ' (' . substr($response, 4) . ')';
            
            return false;
        }
        
        return true;
    }
   
    /**
     * execute the smtp connection.
     *
     * @return boolean
     */
    private function doConnect() {
        
        if( $this->smtpConnect() ) {
            
            $this->makeEhlo();
            
            if( ! empty($this->smtpUsername) ){
                if( ! $this->smtpLogin() )
                    $connection = false;
            }
            
            $connection = true;
        }
           
        if( ! $connection )
            return false;
        
        return $connection;
    }
    
    /**
     * Sending the data to smtp
     *
     * @return boolean
     */
    private function smtpSend() {
       
        if(!$this->doConnect())
            return false;
        
        if( ! $this->smtpFrom())
            return false;
        
        foreach($this->rcptTo as $recipient)
            $this->smtpRecipient($recipient);
        
        if( ! $this->smtpData() )
            return false;
        
        $this->smtpClose();
        
        return true;
    }
    
}