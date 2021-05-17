-- COMPRAS (SOLO CCF)
SET @begin := '2021-02-01';
SET @end := '2021-02-28';
SELECT '#' corr,
	   DATE_FORMAT(f.datef, "%d/%m/%Y") fecha_emision, 
       f.ref_supplier num_documento, 
       s.idprof5 DUI,
       s.siret NCR, 
       s.nom nombre_proveedor, 
       round(sum(0),2) exentas_internas,
       round(sum(0),2) exentas_importaciones,
       round(sum(f.total_ht),2) compras_internas,
       round(sum(0),2) compras_importaciones,
       round(sum(f.total_tva),2) credito_fiscal,
       round(IFNULL(sum(f.total_ttc),0),2) total_compras -- ,
--       round(IFNULL(sum(fe.vat_invoice_retention),0),2) impuesto_retenido     
  FROM `llx_facture_fourn` f,
       `llx_societe` s
 WHERE f.fk_soc = s.rowid
   AND f.ref_supplier LIKE 'CCF%'
   AND f.datef BETWEEN @begin AND @end
GROUP BY 1,2,3,4,5,6
ORDER BY 1,2,3,4,5,6;

-- VENTAS FAC (CONSUMIDOR FINAL)
SET @begin := '2021-04-01';
SET @end := '2021-04-30';
SELECT DATE_FORMAT(f.datef, "%d/%m/%Y") fecha_emision, 
       MIN(TRIM(LEADING '0' FROM TRIM(LEADING 'FEX' FROM TRIM(LEADING 'FAC' FROM f.ref_client)))) del, 
       MAX(TRIM(LEADING '0' FROM TRIM(LEADING 'FEX' FROM TRIM(LEADING 'FAC' FROM f.ref_client)))) al,
       '' caja_num,
       round(sum(CASE WHEN (fe.exenta = 1) THEN f.total ELSE 0 END),2) ventas_externas,
       round(sum(CASE WHEN ((fe.exenta = 0 OR fe.exenta IS NULL) AND (f.ref_client LIKE 'FAC%')) THEN f.total ELSE 0 END),2) ventas_internas_gravadas,
       round(sum(CASE WHEN (fe.exenta = 0 OR fe.exenta IS NULL) AND (f.ref_client LIKE 'FEX%') THEN f.total ELSE 0 END),2) exportaciones,
       round(IFNULL(sum(f.total_ttc),0),2) total_ventas_diarias
  FROM `llx_facture` f,
       `llx_facture_extrafields` fe
 WHERE f.rowid = fe.fk_object
   AND f.ref_client LIKE 'F%'
   AND f.datef BETWEEN @begin AND @end
GROUP BY 1,4
ORDER BY 1,4;

-- VENTAS CCF (CREDITO FISCAL)
SET @begin := '2021-04-01';
SET @end := '2021-04-30';
SELECT @rownum:=@rownum+1 corr, -- TODO: fix this iterator!!!
	   DATE_FORMAT(f.datef, "%d/%m/%Y") fecha_emision, 
       TRIM(LEADING '0' FROM TRIM(LEADING 'CCF' FROM f.ref_client)) corr_pre_impreso, 
       s.nom nombre_contribuyente, 
       s.siret num_registro, 
       round(sum(CASE WHEN (fe.exenta = 1) AND (fe.atercero = 0) THEN f.total ELSE 0 END),2) propias_exentas,
       round(sum(CASE WHEN (fe.exenta = 1) AND (fe.atercero = 0) THEN 0 ELSE f.total END),2) propias_internas_gravadas,
       round(sum(CASE WHEN (fe.atercero = 1) THEN 0 ELSE f.`tva` END),2) propias_debito_fiscal,
       round(sum(CASE WHEN (fe.exenta = 0) AND (fe.atercero = 1) THEN f.total ELSE 0 END),2) a_cuenta_de_exentas,
       round(sum(CASE WHEN (fe.exenta = 0) AND (fe.atercero = 1) THEN f.total ELSE 0 END),2) a_cuenta_de_internas_gravadas,
       round(sum(CASE WHEN (fe.atercero = 1) THEN f.`tva` ELSE 0 END),2) a_cuenta_de_debito_fiscal,
       round(IFNULL(sum(fe.vat_invoice_retention),0),2) impuesto_recibido,
       round(IFNULL(sum(f.total_ttc),0),2) ventas_totales
  FROM `llx_facture` f,
       `llx_facture_extrafields` fe,
       `llx_societe` s,
       (SELECT @rownum := 0) r
 WHERE f.rowid = fe.fk_object
   AND f.fk_soc = s.rowid
   AND f.ref_client LIKE 'CCF%'
   AND f.datef BETWEEN @begin AND @end
GROUP BY 1,2,3,4,5
ORDER BY 1,2,3,4,5;

