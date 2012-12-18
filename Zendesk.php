<?php
/**
 * Wrapper per le API di Zendesk
 *
 * @author Giustino Borzacchiello - giustinob@gmail.com
 *
 *
 */
class Zendesk
{
    /**
     * @var string nome utente dell'amministratore
     */
    private $user;
    /**
     * @var string password dell'amministratore
     */
    private $password;
    /**
     * @var string url di base delle API
     *
     * https://[host].zendesk.com/api/v2/
     */
    private $baseUrl;
    /**
     * @var array opzioni di default per curl
     */
    private $default_options;

    public function __construct($host, $user, $password)
    {
        $this->user = $user;
        $this->password = $password;
        $this->baseUrl = "https://". $host . ".zendesk.com/api/v2/";
        $this->default_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->user . ':' . $this->password,
            CURLOPT_HTTPHEADER => array('Content-type: application/json'),
        );

    }

    /**
     * Recupera tutti gli utenti registrati su Zendesk
     *
     * @return array Array di oggetti utente
     */
    public function getAllUsers()
    {
        $url = $this->baseUrl . "users.json";
        $c = curl_init($url);

        curl_setopt_array( $c, $this->default_options );

        $users = curl_exec($c);
        curl_close($c);
        $users = json_decode($users);

        return $users->users;
    }

    /**
     * Recupera un utente data l'email.
     *
     * L'oggetto restituito contiene solo attributi pubblici.
     * Per la lista di attributi vedere TODO aggiungere lista attributi utente
     *
     * @param $email_cliente string email dell'utente da cercare
     * @return mixed l'oggetto utente oppure false in caso di errore
     */
    public function getUserByEmail($email_cliente)
    {
        $url = $this->baseUrl . "users/search.json?query=type:user%20" . $email_cliente;
        $c = curl_init($url);

        curl_setopt_array( $c, $this->default_options );

        $user = curl_exec($c);
        curl_close($c);
        $user = json_decode($user);
        return $user->users[0];
    }

    /**
     * Aggiunge dei tag all'utente
     *
     * @param object $user oggetto utente recuperato tramite getUserByEmail
     * @param array $tags  array di tag da aggiungere all'utente su zendesk
     *
     * @return mixed L'oggetto utente oppure false in caso di errore
     */
    public function addTagToUser($user, array $tags)
    {
        $id = $user->id;
        $old_tags = $user->tags;
        $tags = array_merge($old_tags, $tags);

        $url = $this->baseUrl . "users/".$id.".json";

        $data = array('user' => array(
            'tags' => $tags
        ));
        $data_str = json_encode($data);

        $c = curl_init($url);

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->user . ':' . $this->password,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => array(
                'Content-type: application/json',
                'Content-Length: ' . strlen($data_str)
            ),
            CURLOPT_POSTFIELDS => $data_str,
        );

        curl_setopt_array( $c, $options );
        return curl_exec($c);
    }
}
