<?php
interface Swift_Mime_ParameterizedHeader extends Swift_Mime_Header
{
    public function setParameter($parameter, $value);
    public function getParameter($parameter);
}
