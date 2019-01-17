<?php

namespace Monolith\Casterlith;

class Configuration extends \Doctrine\DBAL\Configuration
{
	/**
	 * Set the replacer to use when building aliases in selection
	 *
	 * @param  string $replacer
	 * @return null
	 */
	public function setSelectionReplacer($replacer = "cl")
	{
		$this->_attributes['replacer'] = $replacer;
	}

	/**
	 * Get the replacer to use when building aliases in selection
	 *
	 * @return string
	 */
	public function getSelectionReplacer()
	{
		return isset($this->_attributes['replacer']) ? $this->_attributes['replacer'] : "cl";
	}

}
