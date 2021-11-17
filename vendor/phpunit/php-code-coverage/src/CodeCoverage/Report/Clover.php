<?php
class PHP_CodeCoverage_Report_Clover
{
    public function process(PHP_CodeCoverage $coverage, $target = null, $name = null)
    {
        $xmlDocument = new DOMDocument('1.0', 'UTF-8');
        $xmlDocument->formatOutput = true;
        $xmlCoverage = $xmlDocument->createElement('coverage');
        $xmlCoverage->setAttribute('generated', (int) $_SERVER['REQUEST_TIME']);
        $xmlDocument->appendChild($xmlCoverage);
        $xmlProject = $xmlDocument->createElement('project');
        $xmlProject->setAttribute('timestamp', (int) $_SERVER['REQUEST_TIME']);
        if (is_string($name)) {
            $xmlProject->setAttribute('name', $name);
        }
        $xmlCoverage->appendChild($xmlProject);
        $packages = array();
        $report   = $coverage->getReport();
        unset($coverage);
        foreach ($report as $item) {
            $namespace = 'global';
            if (!$item instanceof PHP_CodeCoverage_Report_Node_File) {
                continue;
            }
            $xmlFile = $xmlDocument->createElement('file');
            $xmlFile->setAttribute('name', $item->getPath());
            $classes  = $item->getClassesAndTraits();
            $coverage = $item->getCoverageData();
            $lines    = array();
            foreach ($classes as $className => $class) {
                $classStatements        = 0;
                $coveredClassStatements = 0;
                $coveredMethods         = 0;
                $classMethods           = 0;
                foreach ($class['methods'] as $methodName => $method) {
                    if ($method['executableLines']  == 0) {
                        continue;
                    }
                    $classMethods++;
                    $classStatements        += $method['executableLines'];
                    $coveredClassStatements += $method['executedLines'];
                    if ($method['coverage'] == 100) {
                        $coveredMethods++;
                    }
                    $methodCount = 0;
                    for ($i  = $method['startLine'];
                         $i <= $method['endLine'];
                         $i++) {
                        if (isset($coverage[$i]) && ($coverage[$i] !== null)) {
                            $methodCount = max($methodCount, count($coverage[$i]));
                        }
                    }
                    $lines[$method['startLine']] = array(
                        'count' => $methodCount,
                        'crap'  => $method['crap'],
                        'type'  => 'method',
                        'name'  => $methodName
                    );
                }
                if (!empty($class['package']['namespace'])) {
                    $namespace = $class['package']['namespace'];
                }
                $xmlClass = $xmlDocument->createElement('class');
                $xmlClass->setAttribute('name', $className);
                $xmlClass->setAttribute('namespace', $namespace);
                if (!empty($class['package']['fullPackage'])) {
                    $xmlClass->setAttribute(
                        'fullPackage',
                        $class['package']['fullPackage']
                    );
                }
                if (!empty($class['package']['category'])) {
                    $xmlClass->setAttribute(
                        'category',
                        $class['package']['category']
                    );
                }
                if (!empty($class['package']['package'])) {
                    $xmlClass->setAttribute(
                        'package',
                        $class['package']['package']
                    );
                }
                if (!empty($class['package']['subpackage'])) {
                    $xmlClass->setAttribute(
                        'subpackage',
                        $class['package']['subpackage']
                    );
                }
                $xmlFile->appendChild($xmlClass);
                $xmlMetrics = $xmlDocument->createElement('metrics');
                $xmlMetrics->setAttribute('methods', $classMethods);
                $xmlMetrics->setAttribute('coveredmethods', $coveredMethods);
                $xmlMetrics->setAttribute('conditionals', 0);
                $xmlMetrics->setAttribute('coveredconditionals', 0);
                $xmlMetrics->setAttribute('statements', $classStatements);
                $xmlMetrics->setAttribute(
                    'coveredstatements',
                    $coveredClassStatements
                );
                $xmlMetrics->setAttribute(
                    'elements',
                    $classMethods +
                    $classStatements
                );
                $xmlMetrics->setAttribute(
                    'coveredelements',
                    $coveredMethods +
                    $coveredClassStatements
                );
                $xmlClass->appendChild($xmlMetrics);
            }
            foreach ($coverage as $line => $data) {
                if ($data === null || isset($lines[$line])) {
                    continue;
                }
                $lines[$line] = array(
                    'count' => count($data), 'type' => 'stmt'
                );
            }
            ksort($lines);
            foreach ($lines as $line => $data) {
                $xmlLine = $xmlDocument->createElement('line');
                $xmlLine->setAttribute('num', $line);
                $xmlLine->setAttribute('type', $data['type']);
                if (isset($data['name'])) {
                    $xmlLine->setAttribute('name', $data['name']);
                }
                if (isset($data['crap'])) {
                    $xmlLine->setAttribute('crap', $data['crap']);
                }
                $xmlLine->setAttribute('count', $data['count']);
                $xmlFile->appendChild($xmlLine);
            }
            $linesOfCode = $item->getLinesOfCode();
            $xmlMetrics = $xmlDocument->createElement('metrics');
            $xmlMetrics->setAttribute('loc', $linesOfCode['loc']);
            $xmlMetrics->setAttribute('ncloc', $linesOfCode['ncloc']);
            $xmlMetrics->setAttribute('classes', $item->getNumClassesAndTraits());
            $xmlMetrics->setAttribute('methods', $item->getNumMethods());
            $xmlMetrics->setAttribute(
                'coveredmethods',
                $item->getNumTestedMethods()
            );
            $xmlMetrics->setAttribute('conditionals', 0);
            $xmlMetrics->setAttribute('coveredconditionals', 0);
            $xmlMetrics->setAttribute(
                'statements',
                $item->getNumExecutableLines()
            );
            $xmlMetrics->setAttribute(
                'coveredstatements',
                $item->getNumExecutedLines()
            );
            $xmlMetrics->setAttribute(
                'elements',
                $item->getNumMethods() + $item->getNumExecutableLines()
            );
            $xmlMetrics->setAttribute(
                'coveredelements',
                $item->getNumTestedMethods() + $item->getNumExecutedLines()
            );
            $xmlFile->appendChild($xmlMetrics);
            if ($namespace == 'global') {
                $xmlProject->appendChild($xmlFile);
            } else {
                if (!isset($packages[$namespace])) {
                    $packages[$namespace] = $xmlDocument->createElement(
                        'package'
                    );
                    $packages[$namespace]->setAttribute('name', $namespace);
                    $xmlProject->appendChild($packages[$namespace]);
                }
                $packages[$namespace]->appendChild($xmlFile);
            }
        }
        $linesOfCode = $report->getLinesOfCode();
        $xmlMetrics = $xmlDocument->createElement('metrics');
        $xmlMetrics->setAttribute('files', count($report));
        $xmlMetrics->setAttribute('loc', $linesOfCode['loc']);
        $xmlMetrics->setAttribute('ncloc', $linesOfCode['ncloc']);
        $xmlMetrics->setAttribute(
            'classes',
            $report->getNumClassesAndTraits()
        );
        $xmlMetrics->setAttribute('methods', $report->getNumMethods());
        $xmlMetrics->setAttribute(
            'coveredmethods',
            $report->getNumTestedMethods()
        );
        $xmlMetrics->setAttribute('conditionals', 0);
        $xmlMetrics->setAttribute('coveredconditionals', 0);
        $xmlMetrics->setAttribute(
            'statements',
            $report->getNumExecutableLines()
        );
        $xmlMetrics->setAttribute(
            'coveredstatements',
            $report->getNumExecutedLines()
        );
        $xmlMetrics->setAttribute(
            'elements',
            $report->getNumMethods() + $report->getNumExecutableLines()
        );
        $xmlMetrics->setAttribute(
            'coveredelements',
            $report->getNumTestedMethods() + $report->getNumExecutedLines()
        );
        $xmlProject->appendChild($xmlMetrics);
        if ($target !== null) {
            if (!is_dir(dirname($target))) {
                mkdir(dirname($target), 0777, true);
            }
            return $xmlDocument->save($target);
        } else {
            return $xmlDocument->saveXML();
        }
    }
}
