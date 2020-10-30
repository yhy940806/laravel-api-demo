<?php

namespace App\Exceptions;

use App\Models\Soundblock\Project;
use Exception;

class CreateProjectException extends Exception
{
    //
    protected $objProject;

    public function __construct(Project $objProject = null, $message = "", $code = 0, Exception $previous = null)
    {
        $this->objProject = $objProject;
        $code = 417;
        $message = $message;

        parent::__construct($message, $code, $previous);
    }

    public static function collectionExistsAlready(Project $objProject, $message = "", $code = 417, Exception $previous = null)
    {
        if ($message == "")
        {
            $message = sprintf("Project (%s) has collection already", $objProject->project_uuid);
        }

        return(new static($objProject, $message, $code, $previous));
    }
}
