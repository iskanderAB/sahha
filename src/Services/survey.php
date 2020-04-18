<?php
namespace App\Services;
use App\Repository\SurveyRepository ;

class survey
{
      private $survey ;
      public function __construct(SurveyRepository $survey)
      {
          $this->survey = $survey->findAll();
      }


}