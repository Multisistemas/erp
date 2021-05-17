-- COMPRAS (SOLO CCF)
SET @begin := '2021-04-01';
SET @end := '2021-04-30';
SELECT DATE_FORMAT(f.datef, "%d/%m/%Y") fecha_emision, 
	   1 clase,
	   '03' tipo,
	   TRIM(LEADING '0' FROM TRIM(LEADING 'CCF' FROM f.ref_supplier)) as num_documento,
       s.siret NCR, 
       s.nom nombre_proveedor, 
       round(sum(0),2) compras_exentas,
       round(sum(0),2) internaciones_exentas,
       round(sum(0),2) internaciones_exentas_no_sujetas,
       round(sum(f.total_ht),2) compras_internas_gravadas,
       round(sum(0),2) internaciones_gravadas_bienes,
       round(sum(0),2) importaciones_gravadas_bienes,
       round(sum(0),2) importaciones_gravadas_servicios,
       round(sum(f.total_tva),2) credito_fiscal,
       round(IFNULL(sum(f.total_ttc),0),2) total_compras,
       3 numero_anexo     
  FROM `llx_facture_fourn` f,
       `llx_societe` s
 WHERE f.fk_soc = s.rowid
   AND f.ref_supplier LIKE 'CCF%'
   AND f.datef BETWEEN @begin AND @end
GROUP BY 1,2,3,4,5,6,16
ORDER BY 1,2,3,4,5,6,16;
