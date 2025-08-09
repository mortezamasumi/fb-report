<?php

namespace Mortezamasumi\FbReport\Reports;

use Illuminate\Support\Facades\App;

class Mpdf extends \Mpdf\Mpdf
{
    protected function aliasReplace($html, $PAGENO, $NbPgGp, $NbPg)
    {
        if (App::getLocale() !== 'fa') {
            return parent::aliasReplace($html, $PAGENO, $NbPgGp, $NbPg);
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $PAGENO = str_replace($english, $persian, $PAGENO);
        $NbPgGp = str_replace($english, $persian, $NbPgGp);
        $NbPg = str_replace($english, $persian, $NbPg);

        // Replaces for header and footer
        $html = str_replace('{PAGENO}', $PAGENO, $html);
        $html = str_replace($this->aliasNbPgGp, $NbPgGp, $html);  // {nbpg}
        $html = str_replace($this->aliasNbPg, $NbPg, $html);  // {nb}

        // Replaces for the body
        $html = str_replace(mb_convert_encoding('{PAGENO}', 'UTF-16BE', 'UTF-8'), mb_convert_encoding($PAGENO, 'UTF-16BE', 'UTF-8'), $html);
        $html = str_replace(mb_convert_encoding($this->aliasNbPgGp, 'UTF-16BE', 'UTF-8'), mb_convert_encoding($NbPgGp, 'UTF-16BE', 'UTF-8'), $html);  // {nbpg}
        $html = str_replace(mb_convert_encoding($this->aliasNbPg, 'UTF-16BE', 'UTF-8'), mb_convert_encoding($NbPg, 'UTF-16BE', 'UTF-8'), $html);  // {nb}

        // Date replace
        $html = preg_replace_callback('/\{DATE\s+(.*?)\}/', [$this, 'date_callback'], $html);  // mPDF 5.7

        return $html;
    }
}
