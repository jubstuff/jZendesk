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
     * @var string nome del sottodominio zendesk
     *
     * [host].zendesk.com
     */
    private $host;

    public function __construct($host, $user, $password)
    {
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
    }

    /**
     * Recupera tutti gli utenti registrati su Zendesk
     *
     * @return array Array di oggetti utente
     */
    public function getAllUsers()
    {
        $url = "https://". $this->host . ".zendesk.com/api/v2/users.json";
        $c = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->user . ':' . $this->password,
            CURLOPT_HTTPHEADER => array('Content-type: application/json'),
        );

        curl_setopt_array( $c, $options );

        $users = curl_exec($c);
        curl_close($c);
        $users = json_decode($users);

        return $users->users;
    }

    /**
     * Recupera un utente data l'email.
     *
     * L'oggetto restituito contiene solo attributi pubblici.
     * Per la lista di attributi vedere TODO aggiungere lista attributi
     *
     * @param $email_cliente string email dell'utente da cercare
     * @return object utente
     */
    public function getUserByEmail($email_cliente)
    {
        $url = "https://" . $this->host . ".zendesk.com/api/v2/users/search.json?query=type:user%20" . $email_cliente;
        $c = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->user . ':' . $this->password,
            CURLOPT_HTTPHEADER => array('Content-type: application/json'),
        );

        curl_setopt_array( $c, $options );

        $user = curl_exec($c);
        curl_close($c);
        $user = json_decode($user);
        return $user->users[0];
    }

    /**
     * @param $user
     * @param array $tags
     */
    public function addTagToUser($user, array $tags)
    {
        $id = $user->id;
        $old_tags = $user->tags;
        $tags = array_merge($old_tags, $tags);

        $url = "https://".$this->host.".zendesk.com/api/v2/users/".$id.".json";

        $data = array('user' => array(
            'tags' => $tags
        ));
        $data_str = json_encode($data);

        $c = curl_init($url);

        $options = array(
            CURLOPT_USERPWD => $this->user . ':' . $this->password,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'Content-type: application/json',
                'Content-Length: ' . strlen($data_str)
            ),
            CURLOPT_POSTFIELDS => $data_str,
        );

        curl_setopt_array( $c, $options );
        curl_exec($c);
    }
}
