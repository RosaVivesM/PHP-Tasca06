<?php

namespace Core\DAO;
use Core\App;
use Core\Database;

class NoteDaoFactory
{
    public static function create(): NoteDao
    {
        $db = App::resolve(Database::class);
        return new NoteDaoImpl($db);
    }
}
