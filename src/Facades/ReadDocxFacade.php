<?php
namespace GFL\ReadDocx\Facades;
use Illuminate\Support\Facades\Facade;
class ReadDocxFacade extends Facade
{
    protected static function getFacadeAccessor() {
      return 'ReadDocx';
    }
}