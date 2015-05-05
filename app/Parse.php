<?php namespace app;


class Parse {

    protected $code;

    protected $rawCode;

    protected $rawCodeTable;

    protected $codeTable;

    protected $errors = [];

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }


    protected $patterns = [
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

    protected $language_keywords = [
        'procedure', 'TObject', 'var',
        'set of char', 'char', 'string',
        'integer', 'begin', 'for', 'to',
        'do', 'if', 'in', 'then', 'else',
        'end'
    ];

    protected $language_splitters = [
        ':=', '.', '(', ')', ':', ';',
        ',', '[', ']', '=', '+',
        '-', '>', '<', '\''
    ];

    protected $reg_pattern = [
        '/:=/', '/\./', '/\(/', '/\)/', '/:/', '/;/',
        '/,/', '/\[/', '/\]/', '/\=/', '/\+/',
        '/\-/', '/>/', '/</', '/\'/'
    ];

    protected $reg_replacement = [
        ' := ', ' . ', ' ( ', ' ) ', ' : ', ' ; ',
        ' , ', ' [ ', ' ] ', ' = ', ' + ',
        ' - ', ' > ', ' < ', ' \' '
    ];

    protected $keywords = [];


    protected $splitters = [];

    protected $variables = [];

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

    public function create($code)
    {
        $this->setCode($code);
        $this->setRaw($code);
        $this->setCodeTable($this->rawCode);
        $this->createTypeTables($this->rawCodeTable);
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

    public function getRawCode()
    {
        return $this->rawCode;
    }

    public function getCodeTable()
    {
        return $this->codeTable;
    }

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
        $code = preg_replace($pattern, $replacement, $code);
        // Tiek izdzēsts WHITESPACES(TAB, NEWLINE, RETURN...)
        // un novāktas visas atstarpe kas ir vairāk par vienu
        $code = preg_replace('/\s+/',' ',$code);
        $code = preg_replace('/: =/',':=',$code);
        return $code;
    }

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

    private function setCodeTable($rawCode)
    {
        $this->rawCodeTable = $this->createCodeTable($rawCode);
    }

    private function createTypeTables($rawCodeTable)
    {
        $string = false;
        $openStep = 0;
        $i = 0;

        foreach($rawCodeTable as $chunk)
        {
            $i++; // Soļu skaitītājs

            // Pārbauda vai netiek atvērts brīvais teksts
            if($chunk == "'" && !$string)
            {
                $string = true;
                $openStep = $i;
                $this->addAsSplitter($chunk);
                $elementId = $this->getElementId($this->splitters, $chunk);
                $this->addCodeTableAs('splitter', $chunk, $elementId);
            }
            // Pārbauda vai netiek aizvērts brīvais teksts
            if ($chunk == "'" && $string && $openStep !== $i) {
                $string = false;
            }

            // Pievieno kā literāli ja ir atvērts kā brīvais teksts
            if($string == true && $openStep !== $i)
            {
                $this->addAsLiteral($chunk);
                $elementId = $this->getElementId($this->literals, $chunk);
                $this->addCodeTableAs('literal', $chunk, $elementId);
            }

            // Ja kods ir literālis un nav brīvais teksts
            if($this->checkType($chunk) == 'literal' && !$string)
            {
                $this->addAsLiteral($chunk);
                $elementId = $this->getElementId($this->literals, $chunk);
                $this->addCodeTableAs('literal', $chunk, $elementId);
            }

            // Ja kods ir atslēgas vārds un nav brīvais teksts
            if($this->checkType($chunk) == 'keyword' && !$string)
            {
                $this->addAsKeyword($chunk);
                $elementId = $this->getElementId($this->keywords, $chunk);
                $this->addCodeTableAs('keyword', $chunk, $elementId);
            }

            // Ja kods ir atdalītājs un nav brīvais teksts
            if($this->checkType($chunk) == 'splitter' && !$string)
            {
                $this->addAsSplitter($chunk);
                $elementId = $this->getElementId($this->splitters, $chunk);
                $this->addCodeTableAs('splitter', $chunk, $elementId);
            }

            // Ja kods ir mainīgais un nav brīvais teksts
            if($this->checkType($chunk) == 'variable' && !$string && $chunk !== "")
            {
                $this->addAsVariable($chunk);
                $elementId = $this->getElementId($this->variables, $chunk);
                $this->addCodeTableAs('variable', $chunk, $elementId);
            }
        }

    }

    private function addAsLiteral($chunk)
    {
        if(is_null($this->getElementId($this->literals, $chunk))) {
            $this->literals[] = $chunk;
        }
    }

    private function addAsKeyword($chunk)
    {
        if(is_null($this->getElementId($this->keywords, $chunk))) {
            $this->keywords[] = $chunk;
        }
    }

    private function addAsSplitter($chunk)
    {
        if(is_null($this->getElementId($this->splitters, $chunk))) {
            $this->splitters[] = $chunk;
        }
    }

    private function addAsVariable($chunk)
    {
        if(is_null($this->getElementId($this->variables, $chunk))) {
            $this->variables[] = $chunk;
        }
    }

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

        return 'variable';
    }

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

    private function addCodeTableAs($string, $chunk, $elementId)
    {
        $this->codeTable[] = [
            'element' => $chunk,
            'type' => $string,
            'type_id' => $elementId
        ];
    }

    private function checkSyntax($codeTable)
    {
        $i = 0;
        $j = 0;
        // Hard coded for my lab example
        foreach($this->patterns['for'] as $value)
        {
            if($value !== $codeTable[$i]['type'])
            {
                $this->errors[] = "syntax error in $i step";
            }
            $i++;
        }
    }


}