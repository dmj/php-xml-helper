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

/**
 * Return PHP representation of XML encoded JSON document.
 *
 * Expects a JSON document encoded in XML as specified by XSLT 3 and
 * returns the corresponding PHP structure.
 *
 * @param  DOMElement  $node
 * @param  ArrayObject $map
 * @return mixed
 */
function jsonxml2php (DOMElement $node, ArrayObject $map = null) {
    if ($node->namespaceURI !== 'http://www.w3.org/2005/xpath-functions') {
        throw new RuntimeException(sprintf("Expected the namespace URI to be 'http://www.w3.org/2005/xpath-functions', got '%s'", $node->namespaceURI));
    }
    switch ($node->localName) {
    case 'boolean':
        switch ($node->textContent) {
        case 'true':
            $value = true;
            break;
        case 'false':
            $value = false;
            break;
        default:
            throw new RuntimeException(sprintf("A boolean must be encoded as 'true' or 'false', got '%s'", $node->textContent));
        }
        break;
    case 'number':
        $value = (float)$node->textContent;
        break;
    case 'string':
        $value = (string)$node->textContent;
        break;
    case 'null':
        $value = null;
        break;
    case 'array':
        $value = array();
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $value[] = jsonxml2php($child);
            }
        }
        break;
    case 'map':
        $value = new ArrayObject();
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                jsonxml2php($child, $value);
            }
        }
        break;
    default:
        throw new RuntimeException(sprintf("Unknown JSON type '%s'", $node->localName));
    }

    if ($map) {
        if (!$node->hasAttribute('key')) {
            throw new RuntimeException("A member of a map requires to have a @key attribute");
        }
        $map[$node->getAttribute('key')] = $value;
    }

    return $value;

}
