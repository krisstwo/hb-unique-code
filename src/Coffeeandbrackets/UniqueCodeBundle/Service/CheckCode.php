<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Service;



use Doctrine\DBAL\Connection;

class CheckCode {

    private $extern_connection;
    private $local_connection;

    public function __construct(Connection $extern_connection, Connection $local_connection) {
        $this->extern_connection = $extern_connection;
        $this->local_connection = $local_connection;
    }

    /**
     * @param $code
     * @return String
     */
    public function validate($code) {

        $code_extern = $this->extern_connection->fetchArray("SELECT * FROM code WHERE code = '$code'");
        if(empty($code_extern))
            return "Le code unique indiqué n'est pas valide.";

        $code_local = $this->local_connection->fetchAssoc("SELECT * FROM code WHERE code = '$code'");

        if(!empty($code_local['current_status']) && $code_local['current_status'] == 'used')
            return "Le code unique indiqué a déjà été utilisé.";

        if(!empty($code_local['current_status']) && $code_local['current_status'] == 'waiting')
            return "Le code unique indiqué a déjà une demande de reservation en cours. Vous ne pouvez envoyer plusieurs demandes de réservation en même temps.";

        return "ok";
    }
}