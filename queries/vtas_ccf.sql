-- VENTAS CCF (CREDITO FISCAL)
SET @begin := '2021-03-01';
SET @end := '2021-03-31';
SELECT DATE_FORMAT(f.datef, "%d/%m/%Y") fecha_emision, 
	   1 as clase,
	   '03' as tipo,
	   '15041RESCR586212018' as resolucion,
	   '18SD000C' as serie,
       TRIM(LEADING '0' FROM TRIM(LEADING 'CCF' FROM f.ref_client)) as num_corr_doc,
       TRIM(LEADING '0' FROM TRIM(LEADING 'CCF' FROM f.ref_client)) as num_ctrl_int,
       s.siret num_registro, 
       s.nom nombre_contribuyente, 
       round(sum(0),2) vta_exenta,
       round(sum(0),2) vta_no_sujeta,
       round(IFNULL(sum(f.total),0),2) vta_gravada,
       round(sum(f.`tva`),2) debito_fiscal,
       round(sum(0),2) a_cuenta_de_exentas,
       round(sum(0),2) a_cuenta_de_internas_gravadas,
       round(IFNULL(sum(f.total_ttc),0),2) ventas_totales,
       1 as anexo
  FROM `llx_facture` f,
       `llx_facture_extrafields` fe,
       `llx_societe` s,
       (SELECT @rownum := 0) r
 WHERE f.rowid = fe.fk_object
   AND f.fk_soc = s.rowid
   AND f.ref_client LIKE 'CCF%'
   AND f.datef BETWEEN @begin AND @end
GROUP BY 1,2,3,4,5,6,7,8,9,17
ORDER BY 1,2,3,4,5,6,7,8,9,17;
