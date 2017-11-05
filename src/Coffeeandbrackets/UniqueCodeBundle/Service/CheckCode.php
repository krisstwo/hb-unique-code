<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Service;



use Doctrine\DBAL\Connection;

class CheckCode {

	const VALID_CODE = - 1;
	const INVALID_CODE_NOT_FOUND = 0;
	const INVALID_CODE_USED = 1;
	const INVALID_CODE_RESERVED = 2;

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
	        return self::INVALID_CODE_NOT_FOUND;

        $code_local = $this->local_connection->fetchAssoc("SELECT * FROM code WHERE code = '$code'");

        if(!empty($code_local['current_status']) && $code_local['current_status'] == 'used')
	        return self::INVALID_CODE_USED;

        if(!empty($code_local['current_status']) && $code_local['current_status'] == 'waiting')
	        self::INVALID_CODE_RESERVED;

        return self::VALID_CODE;
    }
}