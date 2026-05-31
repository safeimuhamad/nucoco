-- Nucoco ERP sample data
-- Contains: 20 leads, 20 quotations, 20 invoices, plus quotation/invoice items.
-- Import after production_migration_erp_20260531.sql.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";
SET NAMES utf8mb4;

START TRANSACTION;

-- --------------------------------------------------------
-- 20 sample leads
-- --------------------------------------------------------

INSERT INTO `leads` (`name`, `email`, `phone`, `company`, `source`, `interest_type`, `message`, `status`, `created_at`)
SELECT s.`name`, s.`email`, s.`phone`, s.`company`, s.`source`, s.`interest_type`, s.`message`, s.`status`, s.`created_at`
FROM (
    SELECT 'Andi Pratama' name, 'sample-lead-001@nucoco.test' email, '081200000001' phone, 'PT Sinar Abadi' company, 'website' source, 'Coconut Milk' interest_type, 'Need monthly supply quotation.' message, 'proposal' status, '2026-05-01 09:00:00' created_at
    UNION ALL SELECT 'Budi Santoso', 'sample-lead-002@nucoco.test', '081200000002', 'CV Maju Bersama', 'manual', 'Desiccated Coconut', 'Request pricing for local distribution.', 'qualified', '2026-05-02 10:00:00'
    UNION ALL SELECT 'Citra Lestari', 'sample-lead-003@nucoco.test', '081200000003', 'PT Global Teknologi', 'inquiry', 'ERP Implementation', 'Interested in product and ERP support.', 'proposal', '2026-05-03 11:00:00'
    UNION ALL SELECT 'Dewi Anggraini', 'sample-lead-004@nucoco.test', '081200000004', 'PT Cahaya Mandiri', 'website', 'Coconut Oil', 'Need export-ready coconut oil.', 'contacted', '2026-05-04 09:30:00'
    UNION ALL SELECT 'Eko Wijaya', 'sample-lead-005@nucoco.test', '081200000005', 'Yayasan Pendidikan Nusantara', 'manual', 'Coconut Product Supply', 'Need quotation for procurement.', 'new', '2026-05-05 13:00:00'
    UNION ALL SELECT 'Fajar Nugroho', 'sample-lead-006@nucoco.test', '081200000006', 'PT Lestari Food', 'inquiry', 'Bulk Coconut Supply', 'Need bulk supply for production.', 'proposal', '2026-05-06 14:00:00'
    UNION ALL SELECT 'Grace Tan', 'sample-lead-007@nucoco.test', '+65 6222 0007', 'Global Pacific Foods Pte Ltd', 'website', 'International Coconut Supply', 'Request international quotation.', 'qualified', '2026-05-07 10:30:00'
    UNION ALL SELECT 'Hendra Saputra', 'sample-lead-008@nucoco.test', '081200000008', 'CV Agro Makmur', 'manual', 'Packaging & Delivery', 'Need packaged coconut products.', 'proposal', '2026-05-08 15:00:00'
    UNION ALL SELECT 'Ika Permata', 'sample-lead-009@nucoco.test', '081200000009', 'PT Rumah Organik', 'website', 'Organic Coconut Product', 'Need organic product details.', 'contacted', '2026-05-09 16:00:00'
    UNION ALL SELECT 'Jonathan Lee', 'sample-lead-010@nucoco.test', '+60 3222 0010', 'Pacific Foods Ltd', 'inquiry', 'Export Documentation', 'Need product quotation and export docs.', 'proposal', '2026-05-10 09:45:00'
    UNION ALL SELECT 'Kartika Sari', 'sample-lead-011@nucoco.test', '081200000011', 'PT Fresh Market', 'manual', 'Retail Coconut Supply', 'Need regular retail supply.', 'qualified', '2026-05-11 11:20:00'
    UNION ALL SELECT 'Leonardo Putra', 'sample-lead-012@nucoco.test', '081200000012', 'PT Nusantara Retail', 'website', 'Coconut Milk', 'Need quotation for private label.', 'new', '2026-05-12 12:10:00'
    UNION ALL SELECT 'Maya Indah', 'sample-lead-013@nucoco.test', '081200000013', 'CV Berkah Solusi', 'inquiry', 'Consulting Service', 'Need service package quotation.', 'proposal', '2026-05-13 13:30:00'
    UNION ALL SELECT 'Nathan Wong', 'sample-lead-014@nucoco.test', '+65 6222 0014', 'Coconut Trading Pte Ltd', 'website', 'Desiccated Coconut Export', 'Need CIF quotation.', 'qualified', '2026-05-14 09:15:00'
    UNION ALL SELECT 'Olivia Hartono', 'sample-lead-015@nucoco.test', '081200000015', 'PT Teknologi Nusantara', 'manual', 'ERP & Procurement', 'Need quotation for system and supply.', 'proposal', '2026-05-15 10:40:00'
    UNION ALL SELECT 'Prasetyo Adi', 'sample-lead-016@nucoco.test', '081200000016', 'PT Makmur Sentosa', 'website', 'Coconut Oil', 'Need local quotation.', 'contacted', '2026-05-16 11:50:00'
    UNION ALL SELECT 'Queen Amanda', 'sample-lead-017@nucoco.test', '081200000017', 'CV Cipta Karya', 'inquiry', 'VCO Supply', 'Need VCO product quotation.', 'new', '2026-05-17 14:10:00'
    UNION ALL SELECT 'Rian Saputra', 'sample-lead-018@nucoco.test', '081200000018', 'PT Berkah Solusi', 'manual', 'Installation Service', 'Need service quotation.', 'qualified', '2026-05-18 15:20:00'
    UNION ALL SELECT 'Siti Aisyah', 'sample-lead-019@nucoco.test', '081200000019', 'PT Alam Sejahtera', 'website', 'Coconut Sugar', 'Need product availability.', 'proposal', '2026-05-19 16:30:00'
    UNION ALL SELECT 'Thomas Lim', 'sample-lead-020@nucoco.test', '+65 6222 0020', 'Asia Coconut Export Ltd', 'inquiry', 'International Coconut Product', 'Need annual supply quotation.', 'qualified', '2026-05-20 09:05:00'
) s
WHERE NOT EXISTS (SELECT 1 FROM `leads` l WHERE l.`email` = s.`email`);

-- --------------------------------------------------------
-- 20 sample quotations
-- --------------------------------------------------------

INSERT INTO `quotations`
(`quote_number`, `inquiry_id`, `lead_id`, `quote_type`, `customer_name`, `customer_email`, `customer_phone`, `customer_company`, `currency`, `status`, `valid_until`, `notes`, `subtotal`, `discount`, `tax`, `grand_total`, `created_at`)
SELECT s.`quote_number`, NULL, l.`id`, s.`quote_type`, s.`customer_name`, s.`customer_email`, s.`customer_phone`, s.`customer_company`, s.`currency`, s.`status`, s.`valid_until`, s.`notes`, s.`subtotal`, s.`discount`, s.`tax`, s.`grand_total`, s.`created_at`
FROM (
    SELECT 'QTN-SAMPLE-0001' quote_number, 'sample-lead-001@nucoco.test' lead_email, 'local' quote_type, 'PT Sinar Abadi' customer_name, 'sample-lead-001@nucoco.test' customer_email, '081200000001' customer_phone, 'Pengadaan Coconut Milk' customer_company, 'IDR' currency, 'sent' status, '2026-06-01' valid_until, 'Sample quotation data' notes, 125000000.00 subtotal, 5000000.00 discount, 13200000.00 tax, 133200000.00 grand_total, '2026-05-01 09:30:00' created_at
    UNION ALL SELECT 'QTN-SAMPLE-0002','sample-lead-002@nucoco.test','local','CV Maju Bersama','sample-lead-002@nucoco.test','081200000002','Pengadaan Desiccated Coconut','IDR','draft','2026-06-02','Sample quotation data', 45750000.00, 1750000.00, 4840000.00, 48840000.00, '2026-05-02 10:30:00'
    UNION ALL SELECT 'QTN-SAMPLE-0003','sample-lead-003@nucoco.test','local','PT Global Teknologi','sample-lead-003@nucoco.test','081200000003','ERP Implementation Support','IDR','accepted','2026-06-03','Sample quotation data', 98500000.00, 3500000.00, 10450000.00, 105450000.00, '2026-05-03 11:30:00'
    UNION ALL SELECT 'QTN-SAMPLE-0004','sample-lead-004@nucoco.test','local','PT Cahaya Mandiri','sample-lead-004@nucoco.test','081200000004','Pengadaan Coconut Oil','IDR','sent','2026-06-04','Sample quotation data', 67800000.00, 1800000.00, 7260000.00, 73260000.00, '2026-05-04 10:00:00'
    UNION ALL SELECT 'QTN-SAMPLE-0005','sample-lead-005@nucoco.test','local','Yayasan Pendidikan Nusantara','sample-lead-005@nucoco.test','081200000005','Pengadaan Produk Kelapa','IDR','cancelled','2026-06-05','Sample quotation data', 32400000.00, 0.00, 3564000.00, 35964000.00, '2026-05-05 13:30:00'
    UNION ALL SELECT 'QTN-SAMPLE-0006','sample-lead-006@nucoco.test','local','PT Lestari Food','sample-lead-006@nucoco.test','081200000006','Bulk Coconut Supply','IDR','accepted','2026-06-06','Sample quotation data', 67000000.00, 0.00, 7370000.00, 74370000.00, '2026-05-06 14:30:00'
    UNION ALL SELECT 'QTN-SAMPLE-0007','sample-lead-007@nucoco.test','international','Global Pacific Foods Pte Ltd','sample-lead-007@nucoco.test','+65 6222 0007','Coconut Ingredients Export Supply','USD','sent','2026-06-07','Sample international quotation data', 62300.00, 4300.00, 6380.00, 64380.00, '2026-05-07 11:00:00'
    UNION ALL SELECT 'QTN-SAMPLE-0008','sample-lead-008@nucoco.test','local','CV Agro Makmur','sample-lead-008@nucoco.test','081200000008','Packaging & Delivery','IDR','draft','2026-06-08','Sample quotation data', 150000000.00, 5000000.00, 15950000.00, 160950000.00, '2026-05-08 15:30:00'
    UNION ALL SELECT 'QTN-SAMPLE-0009','sample-lead-009@nucoco.test','local','PT Rumah Organik','sample-lead-009@nucoco.test','081200000009','Organic Coconut Product','IDR','rejected','2026-06-09','Sample quotation data', 52000000.00, 0.00, 5720000.00, 57720000.00, '2026-05-09 16:30:00'
    UNION ALL SELECT 'QTN-SAMPLE-0010','sample-lead-010@nucoco.test','international','Pacific Foods Ltd','sample-lead-010@nucoco.test','+60 3222 0010','Export Documentation','USD','accepted','2026-06-10','Sample international quotation data', 18000.00, 0.00, 1980.00, 19980.00, '2026-05-10 10:15:00'
    UNION ALL SELECT 'QTN-SAMPLE-0011','sample-lead-011@nucoco.test','local','PT Fresh Market','sample-lead-011@nucoco.test','081200000011','Retail Coconut Supply','IDR','sent','2026-06-11','Sample quotation data', 112000000.00, 3000000.00, 11990000.00, 120990000.00, '2026-05-11 12:00:00'
    UNION ALL SELECT 'QTN-SAMPLE-0012','sample-lead-012@nucoco.test','local','PT Nusantara Retail','sample-lead-012@nucoco.test','081200000012','Private Label Coconut Milk','IDR','draft','2026-06-12','Sample quotation data', 87500000.00, 2500000.00, 9350000.00, 94350000.00, '2026-05-12 12:40:00'
    UNION ALL SELECT 'QTN-SAMPLE-0013','sample-lead-013@nucoco.test','local','CV Berkah Solusi','sample-lead-013@nucoco.test','081200000013','Consulting Service Package','IDR','accepted','2026-06-13','Sample quotation data', 61000000.00, 1000000.00, 6600000.00, 66600000.00, '2026-05-13 14:00:00'
    UNION ALL SELECT 'QTN-SAMPLE-0014','sample-lead-014@nucoco.test','international','Coconut Trading Pte Ltd','sample-lead-014@nucoco.test','+65 6222 0014','Desiccated Coconut Export','USD','sent','2026-06-14','Sample international quotation data', 24500.00, 500.00, 2640.00, 26640.00, '2026-05-14 09:45:00'
    UNION ALL SELECT 'QTN-SAMPLE-0015','sample-lead-015@nucoco.test','local','PT Teknologi Nusantara','sample-lead-015@nucoco.test','081200000015','ERP & Procurement','IDR','accepted','2026-06-15','Sample quotation data', 245000000.00, 10000000.00, 25850000.00, 260850000.00, '2026-05-15 11:10:00'
    UNION ALL SELECT 'QTN-SAMPLE-0016','sample-lead-016@nucoco.test','local','PT Makmur Sentosa','sample-lead-016@nucoco.test','081200000016','Local Coconut Oil Supply','IDR','sent','2026-06-16','Sample quotation data', 76000000.00, 2000000.00, 8140000.00, 82140000.00, '2026-05-16 12:20:00'
    UNION ALL SELECT 'QTN-SAMPLE-0017','sample-lead-017@nucoco.test','local','CV Cipta Karya','sample-lead-017@nucoco.test','081200000017','VCO Supply','IDR','draft','2026-06-17','Sample quotation data', 75000000.00, 0.00, 8250000.00, 83250000.00, '2026-05-17 14:40:00'
    UNION ALL SELECT 'QTN-SAMPLE-0018','sample-lead-018@nucoco.test','local','PT Berkah Solusi','sample-lead-018@nucoco.test','081200000018','Installation Service','IDR','accepted','2026-06-18','Sample quotation data', 42000000.00, 0.00, 4620000.00, 46620000.00, '2026-05-18 15:50:00'
    UNION ALL SELECT 'QTN-SAMPLE-0019','sample-lead-019@nucoco.test','local','PT Alam Sejahtera','sample-lead-019@nucoco.test','081200000019','Coconut Sugar Supply','IDR','sent','2026-06-19','Sample quotation data', 93000000.00, 3000000.00, 9900000.00, 99900000.00, '2026-05-19 17:00:00'
    UNION ALL SELECT 'QTN-SAMPLE-0020','sample-lead-020@nucoco.test','international','Asia Coconut Export Ltd','sample-lead-020@nucoco.test','+65 6222 0020','Annual Export Supply','USD','sent','2026-06-20','Sample international quotation data', 39000.00, 1500.00, 4125.00, 41625.00, '2026-05-20 09:35:00'
) s
LEFT JOIN `leads` l ON l.`email` = s.`lead_email`
WHERE NOT EXISTS (SELECT 1 FROM `quotations` q WHERE q.`quote_number` = s.`quote_number`);

-- 2 items for each sample quotation.
INSERT INTO `quotation_items`
(`quotation_id`, `item_type`, `reference_id`, `item_name`, `description`, `quantity`, `unit`, `unit_price`, `total`, `sort_order`)
SELECT q.`id`, s.`item_type`, NULL, s.`item_name`, s.`description`, s.`quantity`, s.`unit`, s.`unit_price`, s.`total`, s.`sort_order`
FROM (
    SELECT 'QTN-SAMPLE-0001' quote_number, 'product' item_type, 'Coconut Milk Supply' item_name, 'Standard coconut milk supply with production-ready specification.' description, 2 quantity, 'Package' unit, 43750000.00 unit_price, 87500000.00 total, 1 sort_order
    UNION ALL SELECT 'QTN-SAMPLE-0001','service','Delivery Coordination','Delivery schedule coordination and order fulfillment service.',1,'Service',37500000.00,37500000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0002','product','Desiccated Coconut Supply','Standard desiccated coconut product with consistent quality.',1,'Package',32025000.00,32025000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0002','service','Packaging Support','Packaging support and document preparation service.',1,'Service',13725000.00,13725000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0003','product','ERP Procurement Package','Procurement package with product and implementation support.',1,'Package',68950000.00,68950000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0003','service','Implementation Support','Configuration and user support service.',1,'Service',29550000.00,29550000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0004','product','Coconut Oil Supply','Standard coconut oil supply with quality control.',1,'Package',47460000.00,47460000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0004','service','Delivery Coordination','Delivery schedule coordination and order fulfillment service.',1,'Service',20340000.00,20340000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0005','product','Coconut Product Supply','Standard coconut products for procurement needs.',1,'Package',22680000.00,22680000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0005','service','Procurement Support','Procurement support and document preparation service.',1,'Service',9720000.00,9720000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0006','product','Bulk Coconut Supply','Bulk coconut supply with production-ready specification.',1,'Package',46900000.00,46900000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0006','service','Delivery Coordination','Delivery schedule coordination and order fulfillment service.',1,'Service',20100000.00,20100000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0007','product','Coconut Ingredients Supply','Export-ready coconut ingredients with standard packaging.',20,'MT',2180.50,43610.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0007','service','Export Handling Service','Export documentation and shipment coordination service.',1,'Service',18690.00,18690.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0008','product','Packaged Coconut Product','Packaged coconut products for distribution.',1,'Package',105000000.00,105000000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0008','service','Delivery Coordination','Delivery schedule coordination and order fulfillment service.',1,'Service',45000000.00,45000000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0009','product','Organic Coconut Product','Organic coconut product supply with standard specification.',1,'Package',36400000.00,36400000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0009','service','Quality Documentation','Quality documentation and fulfillment support service.',1,'Service',15600000.00,15600000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0010','product','Export Coconut Product','Export coconut product with ready shipment specification.',10,'MT',1260.00,12600.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0010','service','Export Documentation','Export documentation and logistics coordination.',1,'Service',5400.00,5400.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0011','product','Retail Coconut Supply','Retail coconut product supply with standard packing.',1,'Package',78400000.00,78400000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0011','service','Delivery Coordination','Delivery schedule coordination and order fulfillment service.',1,'Service',33600000.00,33600000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0012','product','Private Label Coconut Milk','Private label coconut milk supply with standard packaging.',1,'Package',61250000.00,61250000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0012','service','Packaging Design Support','Packaging and document support service.',1,'Service',26250000.00,26250000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0013','product','Consulting Starter Package','Business consulting starter package for coconut supply.',1,'Package',42700000.00,42700000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0013','service','Implementation Advisory','Implementation advisory and report preparation service.',1,'Service',18300000.00,18300000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0014','product','Desiccated Coconut Export','Export-ready desiccated coconut with standard packaging.',10,'MT',1715.00,17150.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0014','service','Export Handling Service','Export documentation and shipment coordination service.',1,'Service',7350.00,7350.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0015','product','ERP Procurement Package','Procurement package with product and ERP support.',1,'Package',171500000.00,171500000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0015','service','Implementation Support','Configuration and user support service.',1,'Service',73500000.00,73500000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0016','product','Coconut Oil Local Supply','Local coconut oil supply with standard specification.',1,'Package',53200000.00,53200000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0016','service','Delivery Coordination','Delivery schedule coordination and order fulfillment service.',1,'Service',22800000.00,22800000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0017','product','VCO Supply','Virgin coconut oil supply with quality specification.',1,'Package',52500000.00,52500000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0017','service','Quality Documentation','Quality documentation and fulfillment support service.',1,'Service',22500000.00,22500000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0018','product','Installation Package','Installation package for procurement support.',1,'Package',29400000.00,29400000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0018','service','Configuration Service','Configuration and implementation support service.',1,'Service',12600000.00,12600000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0019','product','Coconut Sugar Supply','Coconut sugar supply with standard packing.',1,'Package',65100000.00,65100000.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0019','service','Delivery Coordination','Delivery schedule coordination and order fulfillment service.',1,'Service',27900000.00,27900000.00,2
    UNION ALL SELECT 'QTN-SAMPLE-0020','product','Annual Coconut Export Supply','Annual export supply package with standard shipment terms.',15,'MT',1820.00,27300.00,1
    UNION ALL SELECT 'QTN-SAMPLE-0020','service','Export Handling Service','Export documentation and annual shipment coordination.',1,'Service',11700.00,11700.00,2
) s
INNER JOIN `quotations` q ON q.`quote_number` = s.`quote_number`
WHERE NOT EXISTS (
    SELECT 1 FROM `quotation_items` qi WHERE qi.`quotation_id` = q.`id` AND qi.`sort_order` = s.`sort_order`
);

-- --------------------------------------------------------
-- 20 sample invoices, one per sample quotation
-- --------------------------------------------------------

INSERT INTO `invoices`
(`invoice_number`, `quotation_id`, `lead_id`, `customer_name`, `customer_email`, `customer_phone`, `customer_company`, `currency`, `status`, `invoice_date`, `due_date`, `bank_account_number`, `bank_account_name`, `bank_branch`, `notes`, `subtotal`, `discount`, `tax`, `grand_total`, `created_at`)
SELECT s.`invoice_number`, q.`id`, q.`lead_id`, q.`customer_name`, q.`customer_email`, q.`customer_phone`, q.`customer_company`, q.`currency`, s.`status`, s.`invoice_date`, s.`due_date`, s.`bank_account_number`, s.`bank_account_name`, s.`bank_branch`, s.`notes`, q.`subtotal`, q.`discount`, q.`tax`, q.`grand_total`, s.`created_at`
FROM (
    SELECT 'INV-SAMPLE-0001' invoice_number, 'QTN-SAMPLE-0001' quote_number, 'sent' status, '2026-05-02' invoice_date, '2026-06-01' due_date, '123456789001' bank_account_number, 'Nucoco' bank_account_name, 'BCA Jakarta' bank_branch, 'Sample invoice data' notes, '2026-05-02 10:00:00' created_at
    UNION ALL SELECT 'INV-SAMPLE-0002','QTN-SAMPLE-0002','draft','2026-05-03','2026-06-02','123456789002','Nucoco','BCA Jakarta','Sample invoice data','2026-05-03 11:00:00'
    UNION ALL SELECT 'INV-SAMPLE-0003','QTN-SAMPLE-0003','paid','2026-05-04','2026-06-03','123456789003','Nucoco','BCA Jakarta','Sample invoice data','2026-05-04 12:00:00'
    UNION ALL SELECT 'INV-SAMPLE-0004','QTN-SAMPLE-0004','sent','2026-05-05','2026-06-04','123456789004','Nucoco','BCA Jakarta','Sample invoice data','2026-05-05 10:30:00'
    UNION ALL SELECT 'INV-SAMPLE-0005','QTN-SAMPLE-0005','cancelled','2026-05-06','2026-06-05','123456789005','Nucoco','BCA Jakarta','Sample invoice data','2026-05-06 14:00:00'
    UNION ALL SELECT 'INV-SAMPLE-0006','QTN-SAMPLE-0006','paid','2026-05-07','2026-06-06','123456789006','Nucoco','BCA Jakarta','Sample invoice data','2026-05-07 15:00:00'
    UNION ALL SELECT 'INV-SAMPLE-0007','QTN-SAMPLE-0007','sent','2026-05-08','2026-06-07','123456789007','Nucoco','BCA Jakarta','Sample invoice data','2026-05-08 11:30:00'
    UNION ALL SELECT 'INV-SAMPLE-0008','QTN-SAMPLE-0008','draft','2026-05-09','2026-06-08','123456789008','Nucoco','BCA Jakarta','Sample invoice data','2026-05-09 16:00:00'
    UNION ALL SELECT 'INV-SAMPLE-0009','QTN-SAMPLE-0009','cancelled','2026-05-10','2026-06-09','123456789009','Nucoco','BCA Jakarta','Sample invoice data','2026-05-10 17:00:00'
    UNION ALL SELECT 'INV-SAMPLE-0010','QTN-SAMPLE-0010','paid','2026-05-11','2026-06-10','123456789010','Nucoco','BCA Jakarta','Sample invoice data','2026-05-11 10:45:00'
    UNION ALL SELECT 'INV-SAMPLE-0011','QTN-SAMPLE-0011','sent','2026-05-12','2026-06-11','123456789011','Nucoco','BCA Jakarta','Sample invoice data','2026-05-12 12:30:00'
    UNION ALL SELECT 'INV-SAMPLE-0012','QTN-SAMPLE-0012','draft','2026-05-13','2026-06-12','123456789012','Nucoco','BCA Jakarta','Sample invoice data','2026-05-13 13:10:00'
    UNION ALL SELECT 'INV-SAMPLE-0013','QTN-SAMPLE-0013','paid','2026-05-14','2026-06-13','123456789013','Nucoco','BCA Jakarta','Sample invoice data','2026-05-14 14:30:00'
    UNION ALL SELECT 'INV-SAMPLE-0014','QTN-SAMPLE-0014','sent','2026-05-15','2026-06-14','123456789014','Nucoco','BCA Jakarta','Sample invoice data','2026-05-15 10:15:00'
    UNION ALL SELECT 'INV-SAMPLE-0015','QTN-SAMPLE-0015','paid','2026-05-16','2026-06-15','123456789015','Nucoco','BCA Jakarta','Sample invoice data','2026-05-16 11:40:00'
    UNION ALL SELECT 'INV-SAMPLE-0016','QTN-SAMPLE-0016','sent','2026-05-17','2026-06-16','123456789016','Nucoco','BCA Jakarta','Sample invoice data','2026-05-17 12:50:00'
    UNION ALL SELECT 'INV-SAMPLE-0017','QTN-SAMPLE-0017','draft','2026-05-18','2026-06-17','123456789017','Nucoco','BCA Jakarta','Sample invoice data','2026-05-18 15:10:00'
    UNION ALL SELECT 'INV-SAMPLE-0018','QTN-SAMPLE-0018','paid','2026-05-19','2026-06-18','123456789018','Nucoco','BCA Jakarta','Sample invoice data','2026-05-19 16:20:00'
    UNION ALL SELECT 'INV-SAMPLE-0019','QTN-SAMPLE-0019','sent','2026-05-20','2026-06-19','123456789019','Nucoco','BCA Jakarta','Sample invoice data','2026-05-20 17:30:00'
    UNION ALL SELECT 'INV-SAMPLE-0020','QTN-SAMPLE-0020','sent','2026-05-21','2026-06-20','123456789020','Nucoco','BCA Jakarta','Sample invoice data','2026-05-21 10:05:00'
) s
INNER JOIN `quotations` q ON q.`quote_number` = s.`quote_number`
WHERE NOT EXISTS (SELECT 1 FROM `invoices` i WHERE i.`invoice_number` = s.`invoice_number`);

INSERT INTO `invoice_items`
(`invoice_id`, `item_type`, `reference_id`, `item_name`, `description`, `quantity`, `unit`, `unit_price`, `total`, `sort_order`)
SELECT i.`id`, qi.`item_type`, qi.`reference_id`, qi.`item_name`, qi.`description`, qi.`quantity`, qi.`unit`, qi.`unit_price`, qi.`total`, qi.`sort_order`
FROM `invoices` i
INNER JOIN `quotations` q ON q.`id` = i.`quotation_id`
INNER JOIN `quotation_items` qi ON qi.`quotation_id` = q.`id`
WHERE i.`invoice_number` LIKE 'INV-SAMPLE-%'
  AND NOT EXISTS (
      SELECT 1 FROM `invoice_items` ii WHERE ii.`invoice_id` = i.`id` AND ii.`sort_order` = qi.`sort_order`
  );

COMMIT;
