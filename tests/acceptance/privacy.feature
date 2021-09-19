Feature: privacy
  In order to maintain user privacy
  As a user
  I need to see that OEmbeds are hidden behind a cookie wall

  Scenario: YouTube OEmbed
    Given I have a page "youtube_embed" with content 
    """
    [embed]https://www.youtube.com/watch?v=nnudAnqyr20[/embed]
    """
    When I am on page "youtube_embed"
    Then I see an element ".oembed-component"

  Scenario: Vimeo OEmbed
    Given I have a page "vimeo_embed" with content 
    """
    [embed]https://vimeo.com/94454127[/embed]
    """
    When I am on page "vimeo_embed"
    Then I see an element ".oembed-component"

  Scenario: No OEmbed
    Given I have a page "no_embed" with content 
    """
    Just a piece of content
    """
    When I am on page "no_embed"
    Then I don't see an element ".oembed-component"