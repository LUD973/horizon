<?php
/**
 * Sélecteur d'intention : « Comment pouvons-nous vous accompagner ? »
 * Intention avant catalogue — la Home guide vers le bon parcours.
 *
 * L'entrée « Travailler avec FCP » de l'IA n'est PAS exposée ici en com
 * publique (ADN). Le recrutement de prestataires est traité en interne.
 *
 * @package fcp-child
 */

if (!defined('ABSPATH')) {
    exit;
}

$fcp_intents = [
    ['label' => __('Organiser un déplacement', 'fcp-child'),      'href' => '/mobilite/'],
    ['label' => __('Organiser un événement', 'fcp-child'),        'href' => '/evenements/'],
    ['label' => __('Découvrir une destination', 'fcp-child'),     'href' => '/experiences/'],
    ['label' => __('Demander un service sur mesure', 'fcp-child'), 'href' => '/demande/'],
    ['label' => __('Découvrir le Membership', 'fcp-child'),       'href' => '/membership/'],
];
?>
<section class="fcp-intent-wrap" aria-labelledby="fcp-intent-title">
    <h2 id="fcp-intent-title" class="fcp-hero__title" style="text-align:center;font-size:var(--fcp-fs-h2);">
        <?php esc_html_e('Comment pouvons-nous vous accompagner ?', 'fcp-child'); ?>
    </h2>
    <div class="fcp-intent">
        <?php foreach ($fcp_intents as $intent) : ?>
            <a class="fcp-intent__card" href="<?php echo esc_url(home_url($intent['href'])); ?>">
                <?php echo esc_html($intent['label']); ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>
