-- Fill sample quotation addresses for print-out checking.

UPDATE `quotations`
SET `customer_address` = CASE `quote_number`
    WHEN 'QTN-SAMPLE-0001' THEN 'Jl. Jend. Sudirman No. 100, Jakarta 10220, Indonesia'
    WHEN 'QTN-SAMPLE-0002' THEN 'Jl. Gatot Subroto No. 18, Bandung 40262, Indonesia'
    WHEN 'QTN-SAMPLE-0003' THEN 'Jl. HR Rasuna Said Kav. 12, Jakarta 12940, Indonesia'
    WHEN 'QTN-SAMPLE-0004' THEN 'Jl. Diponegoro No. 45, Surabaya 60264, Indonesia'
    WHEN 'QTN-SAMPLE-0005' THEN 'Jl. Pendidikan No. 8, Yogyakarta 55281, Indonesia'
    WHEN 'QTN-SAMPLE-0006' THEN 'Jl. Industri Raya No. 22, Tangerang 15135, Indonesia'
    WHEN 'QTN-SAMPLE-0007' THEN '18 Marina Boulevard, Singapore 018980'
    WHEN 'QTN-SAMPLE-0008' THEN 'Jl. Agro Makmur No. 15, Bogor 16143, Indonesia'
    WHEN 'QTN-SAMPLE-0009' THEN 'Jl. Organik Raya No. 9, Depok 16431, Indonesia'
    WHEN 'QTN-SAMPLE-0010' THEN 'Level 12, Menara Pacific, Kuala Lumpur 50450, Malaysia'
    WHEN 'QTN-SAMPLE-0011' THEN 'Jl. Fresh Market No. 21, Bekasi 17113, Indonesia'
    WHEN 'QTN-SAMPLE-0012' THEN 'Jl. Nusantara Retail No. 77, Semarang 50134, Indonesia'
    WHEN 'QTN-SAMPLE-0013' THEN 'Jl. Berkah Solusi No. 31, Malang 65141, Indonesia'
    WHEN 'QTN-SAMPLE-0014' THEN '10 Anson Road, International Plaza, Singapore 079903'
    WHEN 'QTN-SAMPLE-0015' THEN 'Jl. Teknologi Nusantara No. 88, Jakarta 11520, Indonesia'
    WHEN 'QTN-SAMPLE-0016' THEN 'Jl. Makmur Sentosa No. 16, Medan 20112, Indonesia'
    WHEN 'QTN-SAMPLE-0017' THEN 'Jl. Cipta Karya No. 27, Pekanbaru 28125, Indonesia'
    WHEN 'QTN-SAMPLE-0018' THEN 'Jl. Berkah Solusi Timur No. 4, Surabaya 60293, Indonesia'
    WHEN 'QTN-SAMPLE-0019' THEN 'Jl. Alam Sejahtera No. 19, Makassar 90231, Indonesia'
    WHEN 'QTN-SAMPLE-0020' THEN '25 North Bridge Road, Singapore 179104'
    ELSE `customer_address`
END
WHERE `quote_number` IN (
    'QTN-SAMPLE-0001','QTN-SAMPLE-0002','QTN-SAMPLE-0003','QTN-SAMPLE-0004','QTN-SAMPLE-0005',
    'QTN-SAMPLE-0006','QTN-SAMPLE-0007','QTN-SAMPLE-0008','QTN-SAMPLE-0009','QTN-SAMPLE-0010',
    'QTN-SAMPLE-0011','QTN-SAMPLE-0012','QTN-SAMPLE-0013','QTN-SAMPLE-0014','QTN-SAMPLE-0015',
    'QTN-SAMPLE-0016','QTN-SAMPLE-0017','QTN-SAMPLE-0018','QTN-SAMPLE-0019','QTN-SAMPLE-0020'
);
