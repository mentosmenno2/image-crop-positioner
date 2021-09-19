<?php

namespace Clarkson;

use Clarkson\MailStyling;
use Codeception\TestCase\WPTestCase;
use WP_Mock;

class MailStylingTest extends WPTestCase {

	public function email_content_provider(): array {
		return array(
			'Simple text'              => array(
				'Hallo',
				true,
			),
			'Hyperlink'                => array(
				'Hi, i\'m text with a <a href="#">hyperlink</a>',
				false,
			),
			'Paragraph'                => array(
				'<p>Hi, i\'m text within a paragraph</p>',
				false,
			),
			'Div'                      => array(
				'<div>Hi, i\'m text within a div</div>',
				false,
			),
			'Table cell in table'      => array(
				'<table><td>Hi, i\'m text within a table cell inside a table</td></table>',
				false,
			),
			'Table cell without table' => array(
				'<td>Hi, i\'m text within a table cell without a table</td>',
				false,
			),
			'Script'                   => array(
				'<script>alert("alert from a script tag");</script>',
				false,
			),
		);
	}

	/**
	 * @dataProvider email_content_provider
	 */
	public function test_is_plaintext( $mail_content, $response ) {
		$mailstyling = new MailStyling();
		$this->assertEquals( $response, $mailstyling->is_plaintext( $mail_content ) );
	}
}
