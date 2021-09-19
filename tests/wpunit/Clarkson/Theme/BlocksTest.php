<?php

namespace Clarkson\Theme;

use Codeception\TestCase\WPTestCase;

class BlocksTest extends WPTestCase {
	public function test_controls_allowed_blocks() {
		$post                = $this->factory()->post->create_and_get();
		$allowed_block_types = apply_filters( 'allowed_block_types_all', true, $post );
		$this->assertNotContains( 'core/columns', $allowed_block_types );
		$this->assertContains( 'core/image', $allowed_block_types );
	}
}
