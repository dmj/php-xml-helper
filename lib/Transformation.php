<?php

/**
 * This file is part of HAB XML Helpers.
 *
 * HAB XML Helpers is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * HAB XML Helpers is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HAB XML Helpers.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */

namespace HAB\XML;

use DOMDocument;
use XSLTProcessor;
use RuntimeException;

/**
 * An XSL transformation.
 *
 * @author David Maus <maus@hab.de>
 * @copyright (c) 2018 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3 or higher
 */
class Transformation
{
    private $parameters;
    private $stylesheet;
    private $processor;
    private $source;

    public function __construct (DOMDocument $stylesheet)
    {
        $this->stylesheet = $stylesheet;
    }

    public function setSource (DOMDocument $source)
    {
        $this->source = $source;
    }

    public function getSource ()
    {
        return $this->source;
    }

    public function setParameters (array $parameters)
    {
        $this->parameters = $parameters;
    }

    private function getProcessor ()
    {
        if (!$this->processor) {
            $this->processor = new XSLTProcessor();
            if (!$this->processor->importStylesheet($this->stylesheet)) {
                throw new RuntimeException("Error loading transformation stylesheet");
            }
        }
        return $this->processor;
    }

    public function execute ()
    {
        $processor = $this->getProcessor();
        if ($this->parameters) {
            foreach ($this->parameters as $name => $value) {
                $processor->removeParameter(null, $name);
                $processor->setParameter(null, $name, $value);
            }
        }
        return $processor->transformToDoc($this->source);
    }
}
