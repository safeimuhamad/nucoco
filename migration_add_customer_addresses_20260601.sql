-- Add customer address fields for Leads, Quotations, and Invoices.
-- Import this file once on production after the ERP migration.

ALTER TABLE `leads`
    ADD COLUMN IF NOT EXISTS `address` text DEFAULT NULL AFTER `company`;

ALTER TABLE `quotations`
    ADD COLUMN IF NOT EXISTS `customer_address` text DEFAULT NULL AFTER `customer_company`;

ALTER TABLE `invoices`
    ADD COLUMN IF NOT EXISTS `customer_address` text DEFAULT NULL AFTER `customer_company`;
