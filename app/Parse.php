<?php namespace app;


/**
 * Class Parse
 * @package app
 */
class Parse {

    /**
     * @var
     */
    protected $code;

    /**
     * @var
     */
    protected $rawCode;

    /**
     * @var
     */
    protected $rawCodeTable;

    /**
     * @var
     */
    protected $codeTable;

    /**
     * @var
     */
    protected $tables;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }


    /**
     * @var array
     */
    public $patterns = [
        'for' => [
            'keyword',
            'variable',
            'splitter',
            'variable',
            'keyword',
            /*

             '*',
            'do',
            '*',

            */
        ]
    ];

    /**
     * @var array
     */
    protected $language_keywords = [
        'procedure', 'TObject', 'var',
        'set', 'of', 'char', 'string',
        'integer', 'begin', 'for', 'to',
        'do', 'if', 'in', 'then', 'else',
        'end'
    ];

    /**
     * @var array
     */
    protected $language_splitters = [
        ':=', '.', '(', ')', ':', ';',
        ',', '[', ']', '=', '+',
        '-', '>', '<', '\''
    ];

    /**
     * @var array
     */
    protected $reg_pattern = [
        '/:=/', '/\./', '/\(/', '/\)/', '/:/', '/;/',
        '/,/', '/\[/', '/\]/', '/\=/', '/\+/',
        '/\-/', '/>/', '/</', '/\'/', '/\s+/', '/ : = /'
    ];

    /**
     * @var array
     */
    protected $reg_replacement = [
        ' := ', ' . ', ' ( ', ' ) ', ' : ', ' ; ',
        ' , ', ' [ ', ' ] ', ' = ', ' + ',
        ' - ', ' > ', ' < ', ' \' ', ' ', ' := '
    ];

    /**
     * @var array
     */
    protected $keywords = [];


    /**
     * @var array
     */
    protected $splitters = [];

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var array
     */
    protected $literals = [];

    /**
     * @return array
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @return array
     */
    public function getSplitters()
    {
        return $this->splitters;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return array
     */
    public function getLiterals()
    {
        return $this->literals;
    }

    /**
     * @param $code
     */
    public function create($code)
    {
        $this->setCode($code);
        $this->setRaw($code);
        $this->setCodeTable($this->rawCode);
        $this->scanRawCode($this->rawCode);
        $this->checkSyntax($this->codeTable);
    }

    /**
     * Set $code property
     * @param $code
     */
    private function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get $code property
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getRawCode()
    {
        return $this->rawCode;
    }

    /**
     * @return mixed
     */
    public function getCodeTable()
    {
        return $this->codeTable;
    }

    /**
     * @return mixed
     */
    public function raw()
    {
        $pattern = $this->reg_pattern;
        $replacement = $this->reg_replacement;
        return $this->prepareRawCode($this->code, $pattern, $replacement);
    }

    /**
     * Sagatavo kodu bez TAB, NEWLINE... viss kods
     * ir vienā garā rindā ar parastajām atstarpēm,
     * ņemot vērā atdalītājus
     * @param $code
     * @param array $pattern
     * @param array $replacement
     * @return mixed
     */
    public function prepareRawCode($code, $pattern = [], $replacement = []) {
        // Pirms un pēc atdalītājiem saliek atstarpes
        // kā arī izdzēsts WHITESPACES(TAB, NEWLINE, RETURN...)
        // un novāktas visas atstarpe kas ir vairāk par vienu
        $code = preg_replace($pattern, $replacement, $code);
        return $code;
    }

    /**
     * Tiek izveidots masīvs no raw koda rindas
     * @param $code
     * @return array
     */
    public function createCodeTable($code)
    {
        return explode(' ', $code);
    }

    /**
     * Set Raw
     */
    private function setRaw($code)
    {
        $this->rawCode = $this->raw($code);
    }

    /**
     * @param $rawCode
     */
    private function setCodeTable($rawCode)
    {
        $this->rawCodeTable = $this->createCodeTable($rawCode);
    }

     /**
     * @param $type
     * @param $chunk
     * @return int|null|string
     */
    private function addAs($type,$chunk)
    {
        $type = $type."s";
        if(is_null($this->getElementId($this->$type, $chunk))) {
            array_push($this->$type, $chunk);
        }
        return $this->getElementId($this->$type, $chunk);
    }

    /**
     * @param $chunk
     * @return string
     */
    public function checkType($chunk)
    {
        // Ja atslēgas vārds
        foreach($this->language_keywords as $value)
        {
            if($chunk == $value)
                return 'keyword';
        }

        // Ja atdalītājs
        foreach($this->language_splitters as $value)
        {
            if($chunk == $value)
                return 'splitter';
        }

        // Ja ir integer tad literālis
        if((string) intval($chunk) === $chunk) {
            return 'literal';
        }

        return null;
    }

    /**
     * Tiek atgriezta elementa id no tabulas kurā, tas atrodas
     * @param $table
     * @param $chunk
     * @return int|null|string
     */
    public function getElementId($table, $chunk)
    {
        foreach($table as $key => $value)
        {
            if($value === $chunk) {
                return $key;
            }
        }
        return null;
    }

    /**
     * @param $string
     * @param $chunk
     * @param $elementId
     * @param string $status
     */
    private function addCodeTableAs($string, $chunk, $elementId, $status = "Ok")
    {
        $this->codeTable[] = [
            'element' => $chunk,
            'type' => $string,
            'type_id' => $elementId,
            'status' => $status,
        ];
    }

    /**
     * @param $codeTable
     */
    private function checkSyntax($codeTable)
    {
        // Noteikumi FOR sintakses analizātoram
        $rules = [
            ["key" => "element", "value" => "for"], // for
            ["key" => "type", "value" => "variable"], // ID
            ["key" => "element", "value" => ":="], // :=
            ["key" => "type", "value" => "variable"], // ID
            ["key" => "element", "value" => "to"], // to
            ["key" => "type", "value" => "variable"], // ID
            ["key" => "element", "value" => "."], // .
            ["key" => "type", "value" => "variable"], // ID
            ["key" => "element", "value" => "."], // .
            ["key" => "type", "value" => "variable"], // ID
            ["key" => "element", "value" => "-"], // -
            ["key" => "type", "value" => "literal"], // INT
            ["key" => "element", "value" => "do"], // do
            ["key" => "type", "value" => "variable"], // ID
            ["key" => "element", "value" => ":="], // :=
            ["key" => "type", "value" => "variable"], // ID
            ["key" => "element", "value" => "+"], // +
            ["key" => "type", "value" => "variable"], // ID
            ["key" => "element", "value" => "."], // .
            ["key" => "type", "value" => "variable"], // ID
            ["key" => "element", "value" => "["], // [
            ["key" => "type", "value" => "variable"], // ID
            ["key" => "element", "value" => "]"], // ]
            ["key" => "element", "value" => ";"], // ;
        ];

        $c = $this->codeTable;
        for($i=0; $i <= count($c)-1; $i++)
        {
            if($c[$i]['element'] == "for") {
                for ($j = 0; $j <= count($rules) - 1; $j++) {
                    if (isset($c[$i]) and $c[$i][$t = $rules[$j]["key"]] !== $r = $rules[$j]["value"]){
                        $ir = $c[$i][$t = $rules[$j]["key"]];
                        $this->errors[] = "Sintakses kļūda $i. solī, \"$t\" jābūt \"$r\", bet ir \"$ir\"";
                    }
                    $i++;
                }
            }
        }



    }

    /**
     * Scan Raw Code
     * @param $rawCodeTable
     */
    private function scanRawCode($c)
    {
        $i = 0;
        $string = false;

        while($i < strlen($c))
        {
            $lexem = '';
            $type = ''; // INT, ID, LIT, DELIM, ERROR
            $table = '';

            $step = 0;

            while($c[$i] !== " "){

                if($type == '' && ctype_digit($c[$i]) && $string == false)
                    $type = 'INT';

                if($type == '' && ctype_alpha($c[$i]) && $string == false)
                    $type = 'ID';

                if($type == '' && $c[$i] == "'" && $string == false)
                {
                    $step = $i;
                    $string = true;
                    $type = 'DELIM';
                }

                if($type == '' && $c[$i] == "'" && $string == true)
                {
                    $string = false;
                    $type = 'DELIM';
                }


                if($type == '' && $this->checkType($c[$i]) == 'splitter' && $string == false)
                {
                    $type = 'DELIM';
                }

                if($type !== 'LITERAL' && $this->checkType($c[$i]) !== 'splitter' && $string == false && ctype_punct($c[$i]))
                {
                    $type = 'ERROR';
                }

                // Create Lexems

                // Literals
                if($type == 'INT' && ctype_digit($c[$i]) && $string == false)
                {
                    $type = 'INT';
                    $lexem = $lexem . $c[$i];
                }

                if($string == true && $step !== $i)
                {
                    $type = 'LIT';
                    $lexem = $lexem . $c[$i];
                }

                // Keywords or Variables
                if($type == 'ID' && ctype_alnum($c[$i]) && $string == false)
                {
                    $type = 'ID';
                    $lexem = $lexem . $c[$i];
                }

                // Splitters
                if($type == 'DELIM' && $this->checkType($c[$i]) == 'splitter')
                {
                    $type = 'DELIM';
                    $lexem = $lexem . $c[$i];
                }

                // Error
                if($type == 'INT' && ctype_alpha($c[$i]) && $string == false)
                {
                    $type = 'ERROR';
                }

                if($type == 'ERROR')
                {
                    $type = 'ERROR';
                    $lexem = $lexem . $c[$i];
                }


                $i++;
            }

            // Add in tables

            if($lexem !== "" && $type == "INT") {
                $table = 'literal';
                $elementId = $this->addAs($table, $lexem);
                $this->addCodeTableAs($table, $lexem, $elementId);
            }

            if($lexem !== "" && $type == "LIT") {
                $table = 'literal';
                $elementId = $this->addAs($table, $lexem);
                $this->addCodeTableAs($table, $lexem, $elementId);
            }

            if($lexem !== "" && $type == "DELIM") {
                $table = 'splitter';
                $elementId = $this->addAs($table, $lexem);
                $this->addCodeTableAs($table, $lexem, $elementId);
            }

            if($lexem !== "" && $type == "ERROR") {
                $table = 'error';
                $this->addCodeTableAs($table, $lexem, $elementId, "Error");
                $step = count($this->codeTable)-1;
                $error = "Leksikas kļūda $step. solī, \"$lexem\" ";
                $elementId = $this->addAs($table, $error);
            }

            if($lexem !== "" && $type == "ID") {
                if($this->checkType($lexem) == 'keyword') {
                    $table = 'keyword';
                    $elementId = $this->addAs($table, $lexem);
                    $this->addCodeTableAs($table, $lexem, $elementId);
                } else {
                    $table = 'variable';
                    $elementId = $this->addAs($table, $lexem);
                    $this->addCodeTableAs($table, $lexem, $elementId);
                }

            }
            $i++;

        }
    }


}