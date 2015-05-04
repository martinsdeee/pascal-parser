<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Parse;
use Illuminate\Support\Facades\Input;

class ParseController extends Controller {

    public function index()
    {
        return view('parse.index');
    }

    public function store()
    {
        $parse = new Parse;
        $parse->create(Input::get('code'));
        $splitters = $parse->getSplitters();
        $keywords = $parse->getKeywords();
        $literals = $parse->getLiterals();
        $variables = $parse->getVariables();
        $codeTable = $parse->getCodeTable();

        //dd($parse);
        return view('parse.index', compact(['keywords','splitters','literals','variables','codeTable']));
    }
    
}