<?php
/*
 * This source file is proprietary of Beech Applications bv.
 * Created by: Ruud Silvrants
 * Date: 11/05/2022
 * All code (c) Beech Applications bv. all rights reserverd
 */

namespace BeechIt\NewsImporter\Exception;

/**
 * Class AccountAdminNotFoundException
 */
class NewsItemNotFoundException extends \Exception
{
    public const MESSAGE = 'NewsItem not found by guid: %s';

    public function __construct(string $guid, $code = 0, \Throwable $previous = null)
    {
        $message = sprintf(self::MESSAGE, $guid);
        parent::__construct($message, $code, $previous);
    }
}
