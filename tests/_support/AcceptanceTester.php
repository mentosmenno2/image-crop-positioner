<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor {

	use _generated\AcceptanceTesterActions;

	/**
	 * Define custom actions here
	 */

	/**
     * @Given I have a page :arg1 with content :arg2
     */
	public function iHaveAPageWithContent($arg1, $arg2)
	{
		$this->havePageInDatabase(array(
			'post_name'=>$arg1,
			'post_content'=>$arg2
		));	}

   /**
	* @When I am on page :arg1
	*/
	public function iAmOnPage($arg1)
	{
		$this->amOnPage($arg1);
	}

   /**
	* @Then I see an element :arg1
	*/
	public function iSeeAnElement($arg1)
	{
		$this->seeElement($arg1);
	}

	/**
     * @Then I don't see an element :arg1
     */
	public function iDontSeeAnElement($arg1)
	{
		$this->dontSeeElement($arg1);
	}
}
