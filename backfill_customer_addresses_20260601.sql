-- Backfill customer addresses for existing leads, quotations, and invoices.
-- Use this after migration_add_customer_addresses_20260601.sql.

UPDATE `leads`
SET `address` = CASE
    WHEN LOWER(`company`) LIKE '%pt teknologi nusantara%' THEN 'Jl. Teknologi Nusantara No. 88, Jakarta 11520, Indonesia'
    WHEN LOWER(`company`) LIKE '%global pacific foods%' THEN '18 Marina Boulevard, Singapore 018980'
    WHEN LOWER(`company`) LIKE '%pt sinar abadi%' THEN 'Jl. Jend. Sudirman No. 100, Jakarta 10220, Indonesia'
    WHEN LOWER(`company`) LIKE '%cv maju bersama%' THEN 'Jl. Gatot Subroto No. 18, Bandung 40262, Indonesia'
    WHEN LOWER(`company`) LIKE '%pt global teknologi%' THEN 'Jl. HR Rasuna Said Kav. 12, Jakarta 12940, Indonesia'
    WHEN LOWER(`company`) LIKE '%pt cahaya mandiri%' THEN 'Jl. Diponegoro No. 45, Surabaya 60264, Indonesia'
    WHEN LOWER(`company`) LIKE '%yayasan pendidikan nusantara%' THEN 'Jl. Pendidikan No. 8, Yogyakarta 55281, Indonesia'
    WHEN LOWER(`company`) LIKE '%pt lestari food%' THEN 'Jl. Industri Raya No. 22, Tangerang 15135, Indonesia'
    WHEN LOWER(`company`) LIKE '%cv agro makmur%' THEN 'Jl. Agro Makmur No. 15, Bogor 16143, Indonesia'
    WHEN LOWER(`company`) LIKE '%pt rumah organik%' THEN 'Jl. Organik Raya No. 9, Depok 16431, Indonesia'
    WHEN LOWER(`company`) LIKE '%pacific foods ltd%' THEN 'Level 12, Menara Pacific, Kuala Lumpur 50450, Malaysia'
    WHEN LOWER(`company`) LIKE '%pt fresh market%' THEN 'Jl. Fresh Market No. 21, Bekasi 17113, Indonesia'
    WHEN LOWER(`company`) LIKE '%pt nusantara retail%' THEN 'Jl. Nusantara Retail No. 77, Semarang 50134, Indonesia'
    WHEN LOWER(`company`) LIKE '%cv berkah solusi%' THEN 'Jl. Berkah Solusi No. 31, Malang 65141, Indonesia'
    WHEN LOWER(`company`) LIKE '%coconut trading pte ltd%' THEN '10 Anson Road, International Plaza, Singapore 079903'
    WHEN LOWER(`company`) LIKE '%pt makmur sentosa%' THEN 'Jl. Makmur Sentosa No. 16, Medan 20112, Indonesia'
    WHEN LOWER(`company`) LIKE '%cv cipta karya%' THEN 'Jl. Cipta Karya No. 27, Pekanbaru 28125, Indonesia'
    WHEN LOWER(`company`) LIKE '%pt berkah solusi%' THEN 'Jl. Berkah Solusi Timur No. 4, Surabaya 60293, Indonesia'
    WHEN LOWER(`company`) LIKE '%pt alam sejahtera%' THEN 'Jl. Alam Sejahtera No. 19, Makassar 90231, Indonesia'
    WHEN LOWER(`company`) LIKE '%asia coconut export%' THEN '25 North Bridge Road, Singapore 179104'
    ELSE `address`
END
WHERE `address` IS NULL OR `address` = '';

UPDATE `quotations` q
LEFT JOIN `leads` l ON l.`id` = q.`lead_id`
SET q.`customer_address` = CASE
    WHEN COALESCE(l.`address`, '') != '' THEN l.`address`
    WHEN LOWER(q.`customer_name`) LIKE '%pt teknologi nusantara%' THEN 'Jl. Teknologi Nusantara No. 88, Jakarta 11520, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%global pacific foods%' THEN '18 Marina Boulevard, Singapore 018980'
    WHEN LOWER(q.`customer_name`) LIKE '%pt sinar abadi%' THEN 'Jl. Jend. Sudirman No. 100, Jakarta 10220, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%cv maju bersama%' THEN 'Jl. Gatot Subroto No. 18, Bandung 40262, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%pt global teknologi%' THEN 'Jl. HR Rasuna Said Kav. 12, Jakarta 12940, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%pt cahaya mandiri%' THEN 'Jl. Diponegoro No. 45, Surabaya 60264, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%yayasan pendidikan nusantara%' THEN 'Jl. Pendidikan No. 8, Yogyakarta 55281, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%pt lestari food%' THEN 'Jl. Industri Raya No. 22, Tangerang 15135, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%cv agro makmur%' THEN 'Jl. Agro Makmur No. 15, Bogor 16143, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%pt rumah organik%' THEN 'Jl. Organik Raya No. 9, Depok 16431, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%pacific foods ltd%' THEN 'Level 12, Menara Pacific, Kuala Lumpur 50450, Malaysia'
    WHEN LOWER(q.`customer_name`) LIKE '%pt fresh market%' THEN 'Jl. Fresh Market No. 21, Bekasi 17113, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%pt nusantara retail%' THEN 'Jl. Nusantara Retail No. 77, Semarang 50134, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%cv berkah solusi%' THEN 'Jl. Berkah Solusi No. 31, Malang 65141, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%coconut trading pte ltd%' THEN '10 Anson Road, International Plaza, Singapore 079903'
    WHEN LOWER(q.`customer_name`) LIKE '%pt makmur sentosa%' THEN 'Jl. Makmur Sentosa No. 16, Medan 20112, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%cv cipta karya%' THEN 'Jl. Cipta Karya No. 27, Pekanbaru 28125, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%pt berkah solusi%' THEN 'Jl. Berkah Solusi Timur No. 4, Surabaya 60293, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%pt alam sejahtera%' THEN 'Jl. Alam Sejahtera No. 19, Makassar 90231, Indonesia'
    WHEN LOWER(q.`customer_name`) LIKE '%asia coconut export%' THEN '25 North Bridge Road, Singapore 179104'
    ELSE q.`customer_address`
END
WHERE q.`customer_address` IS NULL OR q.`customer_address` = '';

UPDATE `invoices` i
LEFT JOIN `quotations` q ON q.`id` = i.`quotation_id`
LEFT JOIN `leads` l ON l.`id` = i.`lead_id`
SET i.`customer_address` = CASE
    WHEN COALESCE(q.`customer_address`, '') != '' THEN q.`customer_address`
    WHEN COALESCE(l.`address`, '') != '' THEN l.`address`
    WHEN LOWER(i.`customer_name`) LIKE '%global pacific foods%' THEN '18 Marina Boulevard, Singapore 018980'
    WHEN LOWER(i.`customer_name`) LIKE '%pt teknologi nusantara%' THEN 'Jl. Teknologi Nusantara No. 88, Jakarta 11520, Indonesia'
    ELSE i.`customer_address`
END
WHERE i.`customer_address` IS NULL OR i.`customer_address` = '';
