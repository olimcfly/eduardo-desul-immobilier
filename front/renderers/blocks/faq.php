<?php
/**
 * BLOCK RENDERER: FAQ
 * Affiche des questions/réponses en accordéon
 */

if (!isset($blockData)) $blockData = [];

$items = $blockData['items'] ?? [];
$uid = 'faq_' . substr(md5(json_encode($blockData)), 0, 8);
?>

<section style="padding: 80px 24px;">
  <div style="max-width: 800px; margin: 0 auto;">
    <div class="<?php echo $uid; ?>">
      <?php foreach ($items as $index => $item): ?>
        <?php
          $question = htmlspecialchars($item['question'] ?? '');
          $answer = $item['answer'] ?? '';
          $isOpen = $index === 0 ? 'open' : '';
        ?>
        <details style="margin-bottom: 16px; border: 1px solid #e2d9ce; border-radius: 8px; overflow: hidden;" <?php echo $isOpen; ?>>
          <summary style="padding: 20px; cursor: pointer; background: #f9f6f3; font-weight: 700; color: #1a4d7a; user-select: none; display: flex; justify-content: space-between; align-items: center;">
            <?php echo $question; ?>
            <span style="font-size: 20px; margin-left: 12px;">▼</span>
          </summary>
          <div style="padding: 20px; background: #fff; color: #718096; line-height: 1.6;">
            <?php echo $answer; ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<style>
.<?php echo $uid; ?> details[open] summary span {
  transform: rotate(180deg);
}
</style>
