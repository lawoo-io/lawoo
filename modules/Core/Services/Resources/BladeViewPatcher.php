<?php

namespace Modules\Core\Services\Resources;

use Illuminate\Support\Facades\File;
// use Symfony\Component\CssSelector\CssSelectorConverter; // No longer needed as DOMDocument is removed for patching

class BladeViewPatcher
{
    protected string $originalPath;
    protected string $baseHtml;
    protected string $patchedHtml;
    protected array $appliedPatches = []; // To log which patches were applied

    /**
     * Creates a new instance of the BladeViewPatcher from an HTML string.
     *
     * @param string $html The original HTML content.
     * @param string $path The original file path (for saving).
     * @return self
     */
    public static function fromHtml(string $html, string $path): self
    {
        $instance = new self();
        $instance->originalPath = $path;
        $instance->baseHtml = $html;
        $instance->patchedHtml = $html;
        return $instance;
    }

    /**
     * Applies patches to the HTML content.
     *
     * @param string $patchHtml The HTML string containing the <override>-tags.
     * @param bool $patchAllOccurrences If true, all matching occurrences will be patched. Otherwise, only the first.
     * @return self
     */
    public function patchWith(string $patchHtml, bool $patchAllOccurrences = false): self
    {
        echo "DEBUG: patchWith-function started." . PHP_EOL; // DEBUG output
        echo "DEBUG: Content of \$patchHtml UPON entering patchWith:\n" . $patchHtml . PHP_EOL; // NEW DEBUG output

        // NEU: Entferne alle Blade-Kommentare aus dem Patch-HTML, bevor Overrides verarbeitet werden.
        // Dies stellt sicher, dass Kommentare die Erkennung von <override>-Tags nicht stören.
        $patchHtml = preg_replace('/{{--.*?--}}/s', '', $patchHtml);

        echo "DEBUG: Content of \$patchHtml AFTER removing ALL Blade comments:\n" . $patchHtml . PHP_EOL; // NEW DEBUG output

        // Find all override blocks (normal or self-closing)
        // Group 1,2,3 for full tag; Group 4,5 for self-closing tag
        preg_match_all(
            '/<override[^>]*find="(.*?)"[^>]*make="(.*?)"[^>]*>(.*?)<\/override>' .
            '|<override[^>]*find="(.*?)"[^>]*make="(.*?)"[^>]*\/>/is',
            $patchHtml,
            $matches,
            PREG_SET_ORDER
        );

        if (empty($matches)) {
            echo "BladeViewPatcher: No <override>-tags found in the patch HTML. This means no changes will be applied." . PHP_EOL;
        }

        foreach ($matches as $match) {
            $selector = '';
            $action = '';
            $replacement = '';

            // Extract find, make, and replacement content
            if (!empty($match[1]) && !empty($match[2])) { // Full tag
                $selector = $match[1];
                $action   = $match[2];
                $replacement = trim($match[3] ?? '');
            } elseif (!empty($match[4]) && !empty($match[5])) { // Self-closing tag
                $selector = $match[4];
                $action   = $match[5];
                $replacement = '';
            } else {
                echo "BladeViewPatcher: Unexpected match format for override tag. Skipping." . PHP_EOL;
                continue;
            }

            echo "BladeViewPatcher: Processing override - Selector: '" . $selector . "', Action: '" . $action . "'" . PHP_EOL;

            // Parse the current <override> tag itself using DOMDocument to get its attributes (e.g., 'add', 'remove')
            // This is still needed to reliably extract 'add' and 'remove' attributes from the <override> tag itself.
            $overrideDom = new \DOMDocument();
            @$overrideDom->loadHTML('<root>' . $match[0] . '</root>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $overrideElement = $overrideDom->getElementsByTagName('override')->item(0);

            if (!$overrideElement) {
                echo "BladeViewPatcher: Failed to parse <override> tag itself for attributes. Skipping." . PHP_EOL;
                libxml_clear_errors();
                continue;
            }
            libxml_clear_errors(); // Clear any errors from parsing the override tag itself


            $pattern = '';
            $isBladeComponent = false; // Flag to differentiate between HTML and Blade component
            $tagName = ''; // To store the tag name for attribute actions (e.g., 'div' or 'x-component')

            if (str_starts_with($selector, '#')) {
                // Selector is an ID: e.g., #myId
                $id = substr($selector, 1);
                // Capture the full element: $m[0] = full match, $m[1] = opening tag, $m[2] = tag name, $m[3] = inner content, $m[4] = closing tag
                $pattern = '/(<([a-z0-9\-:]+)[^>]*id=["\']' . preg_quote($id, '/') . '["\'][^>]*>)([\s\S]*?)(<\/\2>)/is';
            } elseif (preg_match('/^(\w+)\.([\w\-]+)$/', $selector, $selMatch)) {
                // Selector is Tag.Class: e.g., div.myClass
                $tagName = $selMatch[1];
                $class = $selMatch[2];
                // Capture the full element: $m[0] = full match, $m[1] = opening tag, $m[2] = tag name, $m[3] = inner content, $m[4] = closing tag
                $pattern = '/(<(' . $tagName . ')[\s\S]*?class=["\'][^"\']*\b' . preg_quote($class, '/') . '\b[^"\']*["\'][\s\S]*?>)([\s\S]*?)(<\/\2>)/is';
            } elseif (str_starts_with($selector, 'x-') || str_contains($selector, ':')) { // Added str_contains for flux: components
                // Selector is a Blade Component: e.g., x-web.layouts.sidebar or flux:sidebar.toggle
                $isBladeComponent = true;
                $tagName = $selector; // For x-components/custom components, the selector itself is the tag name
                $componentName = preg_quote($selector, '/');
                // NEW ROBUST REGEX FOR BLADE COMPONENTS (handles self-closing and full tags)
                // m[1]: full opening tag (e.g., <flux:sidebar.toggle ... /> or <x-component ...>)
                // m[2]: attributes part of opening tag (e.g., class="lg:hidden" icon="x-mark")
                // m[3]: the final part of the opening tag (either /> or >)
                // m[4]: inner content (optional)
                // m[5]: closing tag (optional)
                $pattern = '/(<' . $componentName . '(?=\s|>)([^>]*?)(\/?>))([\s\S]*?)(?(3)(<\/' . $componentName . '>))?/is';
            } else {
                echo "BladeViewPatcher: Unrecognized selector format: " . $selector . ". Skipping." . PHP_EOL;
                continue;
            }

            echo "BladeViewPatcher: Generated Regex Pattern for '" . $selector . "': " . $pattern . PHP_EOL; // IMPORTANT DEBUG OUTPUT

            // Set the limit for preg_replace_callback: -1 for all occurrences, 1 for only the first.
            $limit = $patchAllOccurrences ? -1 : 1;

            // Store the current HTML state to check if a patch was applied.
            $initialHtml = $this->patchedHtml;

            $this->patchedHtml = preg_replace_callback($pattern, function ($m) use ($action, $replacement, $selector, $overrideElement, $isBladeComponent) {
                echo "BladeViewPatcher: Selector found: " . $selector . PHP_EOL;
                echo "BladeViewPatcher: Original Match (Start):\n" . substr($m[0], 0, 200) . (strlen($m[0]) > 200 ? '...' : '') . PHP_EOL;

                $openingTagHtml = '';
                $innerContentHtml = '';
                $closingTagHtml = '';
                $isSelfClosing = false;

                if ($isBladeComponent) {
                    // For Blade components:
                    // $m[1] = full opening tag string (e.g., <flux:sidebar.toggle ... />)
                    // $m[2] = attributes part (e.g., class="lg:hidden" icon="x-mark")
                    // $m[3] = closing part of opening tag (/> or >)
                    // $m[4] = inner content (if not self-closing)
                    // $m[5] = closing tag (if not self-closing)
                    $openingTagHtml = $m[1];
                    $isSelfClosing = (isset($m[3]) && $m[3] === '/>'); // Check if the opening tag ends with />

                    if (!$isSelfClosing) {
                        $innerContentHtml = $m[4] ?? ''; // Inner content is in m[4] if not self-closing
                        $closingTagHtml = $m[5] ?? '';   // Closing tag is in m[5] if not self-closing
                    } else {
                        $innerContentHtml = '';
                        $closingTagHtml = '';
                    }

                    echo "BladeViewPatcher: DEBUG: Blade Component Match - m[1] (Full Opening Tag): '" . substr($m[1], 0, 100) . "'";
                    if (isset($m[2])) echo " m[2] (Attributes): '" . substr($m[2], 0, 100) . "'";
                    if (isset($m[3])) echo " m[3] (Tag End): '" . substr($m[3], 0, 100) . "'";
                    if (isset($m[4])) echo " m[4] (Inner Content): '" . substr($m[4], 0, 100) . "'";
                    if (isset($m[5])) echo " m[5] (Closing Tag): '" . substr($m[5], 0, 100) . "'";
                    echo " (Self-Closing: " . ($isSelfClosing ? 'true' : 'false') . ")" . PHP_EOL;

                } else {
                    // For HTML tags (ID or Tag.Class): $m[1]=opening tag, $m[2]=tag name, $m[3]=inner content, $m[4]=closing tag
                    $openingTagHtml = $m[1];
                    $innerContentHtml = $m[3];
                    $closingTagHtml = $m[4];
                    $isSelfClosing = false; // HTML tags are not self-closing in this context for content manipulation
                    echo "BladeViewPatcher: DEBUG: HTML Tag Match - m[1] (Opening Tag): '" . substr($m[1], 0, 100) . "' m[2] (Tag Name): '" . substr($m[2], 0, 100) . "' m[3] (Inner Content): '" . substr($m[3], 0, 100) . "' m[4] (Closing Tag): '" . substr($m[4], 0, 100) . "'" . PHP_EOL;
                }

                $indent = self::indent($m[0]);
                $rep = $indent . trim($replacement);

                $result = $m[0]; // Default to no change

                if (str_starts_with($action, 'attribute-')) {
                    // Handle attribute modification using Regex
                    $modifiedOpeningTag = self::handleAttributePatchRegex($action, $openingTagHtml, $overrideElement);
                    if ($isSelfClosing) {
                        $result = $modifiedOpeningTag; // For self-closing, the result is just the modified opening tag
                    } else {
                        $result = $modifiedOpeningTag . $innerContentHtml . $closingTagHtml;
                    }

                } else {
                    // Handle content manipulation actions
                    switch ($action) {
                        case 'replace':
                            $result = $rep;
                            break;
                        case 'before':
                            $result = $rep . PHP_EOL . ltrim($m[0]);
                            break;
                        case 'after':
                            $result = rtrim($m[0]) . PHP_EOL . $rep;
                            break;
                        case 'inside':
                            if ($isSelfClosing) {
                                echo "BladeViewPatcher: WARNING: 'inside' action not applicable for self-closing tag '" . $selector . "'. Skipping content injection." . PHP_EOL;
                                $result = $m[0]; // No change for self-closing
                            } else {
                                // Insert replacement content before the closing tag within the matched element's content
                                $finalInnerContent = rtrim($innerContentHtml) . PHP_EOL . $indent . $rep . PHP_EOL;
                                $result = $openingTagHtml . $finalInnerContent . $closingTagHtml;
                            }
                            break;
                        default:
                            echo "BladeViewPatcher: Unknown content action '" . $action . "' for selector. Skipping." . PHP_EOL;
                            break;
                    }
                }

                echo "BladeViewPatcher: Action '" . $action . "' applied for selector '" . $selector . "'." . PHP_EOL;
                $this->appliedPatches[] = [
                    'selector' => $selector,
                    'action' => $action,
                    'original_match_start' => strpos($this->baseHtml, $m[0]),
                    'replacement_length' => strlen($rep),
                ];
                return $result;
            }, $this->patchedHtml, $limit);

            if ($initialHtml === $this->patchedHtml) {
                echo "BladeViewPatcher: No match found or no change applied for selector: " . $selector . " with pattern: " . $pattern . PHP_EOL;
            }

            // Remove any remaining Blade comments from the patch HTML (already processed)
            // This line is kept as it might be intended to clean up the patchHtml variable itself,
            // though it doesn't affect the main patching logic for <override> tags.
            // This line is redundant now as all comments are removed at the beginning of the function.
            // $patchHtml = preg_replace('/{{--.*?--}}/s', '', $patchHtml);
        }

        echo "DEBUG: patchWith-function finished." . PHP_EOL; // DEBUG output

        return $this;
    }

    /**
     * Returns the patched HTML content.
     *
     * @return string
     */
    public function render(): string
    {
        return $this->patchedHtml;
    }

    /**
     * Saves the patched HTML content to a file.
     *
     * @param string|null $destination The path where the file should be saved. If null, originalPath is used.
     * @throws \Exception If the file cannot be written.
     */
    public function save(?string $destination = null): void
    {
        $path = $destination ?? $this->originalPath;
        try {
            File::put($path, $this->render());
            echo "BladeViewPatcher: Patched HTML successfully saved to: " . $path . PHP_EOL;
        } catch (\Exception $e) {
            echo "BladeViewPatcher: Error saving patched HTML to " . $path . ": " . $e->getMessage() . PHP_EOL;
            throw new \Exception("Could not save patched HTML to " . $path . ": " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Determines the indentation level of the first line of the given HTML string.
     *
     * @param string $html The HTML string.
     * @return string The leading whitespace (indentation).
     */
    protected static function indent(string $html): string
    {
        if (preg_match('/^([ \t]*)<[^\s>]/m', $html, $match)) {
            return $match[1] ?? '';
        }
        return '';
    }

    /**
     * Handles attribute patching for an opening HTML/Blade tag string using Regex.
     *
     * @param string $action The action string (e.g., 'attribute-class', 'attribute-id').
     * @param string $openingTagHtml The full string of the opening tag (e.g., '<div class="foo">').
     * @param \DOMElement $override The <override> DOM element containing 'add'/'remove' attributes.
     * @return string The modified opening tag string.
     */
    protected static function handleAttributePatchRegex(string $action, string $openingTagHtml, \DOMElement $override): string
    {
        $attribute = substr($action, strlen('attribute-'));
        $add = $override->getAttribute('add');
        $remove = $override->getAttribute('remove');

        echo "BladeViewPatcher: Regex Attribute DEBUG: Processing attribute '" . $attribute . "' for opening tag: '" . $openingTagHtml . "'." . PHP_EOL;
        echo "BladeViewPatcher: Regex Attribute DEBUG: Add: '" . $add . "', Remove: '" . $remove . "'." . PHP_EOL;

        $modifiedTagHtml = $openingTagHtml;

        // Regex to find and capture the specific attribute in the opening tag.
        // This handles single/double quotes and attributes that might not exist.
        // It also handles attributes with no value (e.g., <input required>)
        // NEW: Added support for optional colon prefix for Blade expression attributes (e.g., :icon)
        $attributePattern = '/\s(:?' . preg_quote($attribute, '/') . ')(?:=(["\'])(.*?)\2|([^"\'>\s]+))?/i';
        // $attrMatch[1] = attribute name (e.g., 'icon' or ':icon')
        // $attrMatch[2] = quote char if quoted
        // $attrMatch[3] = quoted value
        // $attrMatch[4] = unquoted value (for cases like <input value=123>)

        if (preg_match($attributePattern, $openingTagHtml, $attrMatch, PREG_OFFSET_CAPTURE)) {
            // Attribute exists.
            $fullAttributeMatch = $attrMatch[0][0]; // e.g., ' class="flex"' or ' :icon="x-mark"'
            $attributeNameFound = $attrMatch[1][0]; // e.g., 'class' or ':icon' (includes colon if present)
            $existingValue = '';

            // Determine existing value based on whether it was quoted or unquoted
            if (isset($attrMatch[3])) { // Quoted value
                $existingValue = $attrMatch[3][0];
            } elseif (isset($attrMatch[4])) { // Unquoted value
                $existingValue = $attrMatch[4][0];
            }

            echo "BladeViewPatcher: Regex Attribute DEBUG: Found existing attribute '" . $attributeNameFound . "' with value: '" . $existingValue . "'." . PHP_EOL;

            $finalValue = $existingValue;
            $newAttributeString = '';

            if ($attribute === 'class') {
                $existingParts = preg_split('/\s+/', $existingValue, -1, PREG_SPLIT_NO_EMPTY);

                if ($remove) {
                    $toRemove = preg_split('/\s+/', $remove, -1, PREG_SPLIT_NO_EMPTY);
                    $existingParts = array_diff($existingParts, $toRemove);
                }
                if ($add) {
                    $toAdd = preg_split('/\s+/', $add, -1, PREG_SPLIT_NO_EMPTY);
                    $existingParts = array_merge($existingParts, $toAdd);
                    $existingParts = array_unique($existingParts);
                }
                $finalValue = trim(implode(' ', $existingParts));
                $newAttributeString = ' ' . $attributeNameFound . '="' . $finalValue . '"'; // Use $attributeNameFound here
            } else {
                // For non-class attributes, 'add' typically means overwrite. 'remove' means clear.
                if ($add) {
                    $finalValue = $add;
                    $newAttributeString = ' ' . $attributeNameFound . '="' . $finalValue . '"'; // Use $attributeNameFound here
                } elseif ($remove) {
                    $finalValue = ''; // If remove is specified and add is not, clear the value.
                    // To remove the attribute completely, we need to replace the full match with empty string.
                    $newAttributeString = '';
                } else {
                    $finalValue = $existingValue; // No add or remove, keep existing.
                    // Reconstruct existing attribute if no change
                    $newAttributeString = ' ' . $attributeNameFound . (empty($existingValue) && !isset($attrMatch[2]) && !isset($attrMatch[4]) ? '' : '="' . $finalValue . '"'); // Handle boolean attributes
                }
            }

            echo "BladeViewPatcher: Regex Attribute DEBUG: Final attribute value: '" . $finalValue . "'." . PHP_EOL;

            // Replace the old attribute string with the new one
            // Use PREG_OFFSET_CAPTURE to get the exact position for replacement
            $modifiedTagHtml = substr_replace($openingTagHtml, $newAttributeString, $attrMatch[0][1], strlen($fullAttributeMatch));

        } else {
            // Attribute does not exist in the opening tag. Add it if 'add' is present.
            if ($add) {
                // Determine if the original attribute had a colon prefix or not based on $attributeNameFound
                // If adding a new attribute, we add it without a colon unless the original attribute was found with one
                // or if the original $attribute (from $action) explicitly started with a colon.
                $attrToAdd = $attribute; // Default to adding without colon
                // Check if the original action implied a colon (e.g., 'attribute-:icon')
                if (str_starts_with($action, 'attribute-:')) {
                    $attrToAdd = ':' . $attribute;
                }

                $newAttribute = ' ' . $attrToAdd . '="' . $add . '"';
                // Insert before the closing '>' of the opening tag
                $modifiedTagHtml = preg_replace('/>$/', $newAttribute . '>', $openingTagHtml, 1);
                echo "BladeViewPatcher: Regex Attribute DEBUG: Attribute '" . $attrToAdd . "' added with value: '" . $add . "'." . PHP_EOL;
            } else {
                echo "BladeViewPatcher: Regex Attribute DEBUG: Attribute '" . $attribute . "' not found and no 'add' value to add it. No change." . PHP_EOL;
            }
        }

        echo "BladeViewPatcher: Regex Attribute DEBUG: Modified opening tag: '" . $modifiedTagHtml . "'." . PHP_EOL;
        return $modifiedTagHtml;
    }

    /**
     * Cleans up HTML output from DOMDocument, removing boilerplate tags like DOCTYPE, html, body.
     * This function is still present for completeness but is not used in the current Regex-only patching logic.
     * It would be used if DOMDocument were re-introduced for certain HTML manipulations.
     *
     * @param string $html The HTML string from DOMDocument->saveHTML().
     * @return string The cleaned HTML string.
     */
    protected static function cleanDomOutput(string $html): string
    {
        // Remove DOCTYPE, <html>, <body> tags added by DOMDocument if they wrap the content
        $html = preg_replace('/<!DOCTYPE html[^>]*>/i', '', $html);
        $html = preg_replace('/<html[^>]*>/i', '', $html);
        $html = preg_replace('/<\/html>/i', '', $html);
        $html = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $html); // Remove head as well
        $html = preg_replace('/<body[^>]*>/i', '', $html);
        $html = preg_replace('/<\/body>/i', '', $html);
        // Remove any leading/trailing whitespace that might be left
        return trim($html);
    }

    /**
     * Returns a log of applied patches.
     * @return array
     */
    public function getAppliedPatches(): array
    {
        return $this->appliedPatches;
    }
}
