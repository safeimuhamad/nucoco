-- Ensure quotation descriptions never use long web descriptions.
-- Run this once on production after pulling the latest code.

ALTER TABLE `products`
    ADD COLUMN IF NOT EXISTS `quotation_description` text DEFAULT NULL AFTER `description`;

ALTER TABLE `services`
    ADD COLUMN IF NOT EXISTS `quotation_description` text DEFAULT NULL AFTER `content`;

UPDATE `products`
SET `quotation_description` = CASE
    WHEN `language` = 'id' THEN CONCAT('Spesifikasi standar untuk ', `name`, '. Cocok untuk kebutuhan penawaran dan pengadaan.')
    ELSE CONCAT('Standard specification for ', `name`, '. Suitable for quotation and supply requirements.')
END
WHERE `quotation_description` IS NULL OR TRIM(`quotation_description`) = '';

UPDATE `services`
SET `quotation_description` = CASE
    WHEN `language` = 'id' THEN CONCAT('Lingkup layanan standar untuk ', `title`, '. Cocok untuk kebutuhan penawaran dan operasional.')
    ELSE CONCAT('Standard service scope for ', `title`, '. Suitable for quotation and operational requirements.')
END
WHERE `quotation_description` IS NULL OR TRIM(`quotation_description`) = '';

UPDATE `quotation_items` qi
INNER JOIN `quotations` q ON q.`id` = qi.`quotation_id`
LEFT JOIN `products` p ON qi.`item_type` = 'product' AND p.`id` = qi.`reference_id`
LEFT JOIN `services` s ON qi.`item_type` = 'service' AND s.`id` = qi.`reference_id`
SET qi.`description` = CASE
    WHEN qi.`item_type` = 'product' AND COALESCE(TRIM(p.`quotation_description`), '') != '' THEN p.`quotation_description`
    WHEN qi.`item_type` = 'service' AND COALESCE(TRIM(s.`quotation_description`), '') != '' THEN s.`quotation_description`
    WHEN q.`quote_type` = 'local' AND qi.`item_type` = 'service' THEN CONCAT('Lingkup layanan standar untuk ', qi.`item_name`, '. Cocok untuk kebutuhan penawaran dan operasional.')
    WHEN q.`quote_type` = 'local' THEN CONCAT('Spesifikasi standar untuk ', qi.`item_name`, '. Cocok untuk kebutuhan penawaran dan pengadaan.')
    WHEN qi.`item_type` = 'service' THEN CONCAT('Standard service scope for ', qi.`item_name`, '. Suitable for quotation and operational requirements.')
    ELSE CONCAT('Standard specification for ', qi.`item_name`, '. Suitable for quotation and supply requirements.')
END
WHERE qi.`description` IS NULL
   OR TRIM(qi.`description`) = ''
   OR CHAR_LENGTH(qi.`description`) > 160;

UPDATE `invoice_items` ii
INNER JOIN `invoices` i ON i.`id` = ii.`invoice_id`
LEFT JOIN `quotations` q ON q.`id` = i.`quotation_id`
LEFT JOIN `products` p ON ii.`item_type` = 'product' AND p.`id` = ii.`reference_id`
LEFT JOIN `services` s ON ii.`item_type` = 'service' AND s.`id` = ii.`reference_id`
SET ii.`description` = CASE
    WHEN ii.`item_type` = 'product' AND COALESCE(TRIM(p.`quotation_description`), '') != '' THEN p.`quotation_description`
    WHEN ii.`item_type` = 'service' AND COALESCE(TRIM(s.`quotation_description`), '') != '' THEN s.`quotation_description`
    WHEN COALESCE(q.`quote_type`, 'local') = 'local' AND ii.`item_type` = 'service' THEN CONCAT('Lingkup layanan standar untuk ', ii.`item_name`, '. Cocok untuk kebutuhan penawaran dan operasional.')
    WHEN COALESCE(q.`quote_type`, 'local') = 'local' THEN CONCAT('Spesifikasi standar untuk ', ii.`item_name`, '. Cocok untuk kebutuhan penawaran dan pengadaan.')
    WHEN ii.`item_type` = 'service' THEN CONCAT('Standard service scope for ', ii.`item_name`, '. Suitable for quotation and operational requirements.')
    ELSE CONCAT('Standard specification for ', ii.`item_name`, '. Suitable for quotation and supply requirements.')
END
WHERE ii.`description` IS NULL
   OR TRIM(ii.`description`) = ''
   OR CHAR_LENGTH(ii.`description`) > 160;
