-- VENTAS FAC (CONSUMIDOR FINAL)
SET @begin := '2021-03-01';
SET @end := '2021-03-31';
SELECT DATE_FORMAT(f.datef, "%d/%m/%Y") as fecha_emision, 
	   1 as clase,
	   '01' as tipo,
	   '15041RESIN298562019' as resolucion,
	   '19DS000F' as serie,
       MIN(TRIM(LEADING '0' FROM TRIM(LEADING 'FEX' FROM TRIM(LEADING 'FAC' FROM f.ref_client)))) del, 
       MAX(TRIM(LEADING '0' FROM TRIM(LEADING 'FEX' FROM TRIM(LEADING 'FAC' FROM f.ref_client)))) al,
       MIN(TRIM(LEADING '0' FROM TRIM(LEADING 'FEX' FROM TRIM(LEADING 'FAC' FROM f.ref_client)))) del, 
       MAX(TRIM(LEADING '0' FROM TRIM(LEADING 'FEX' FROM TRIM(LEADING 'FAC' FROM f.ref_client)))) al,
       '' caja_num,
       round(sum(CASE WHEN (fe.exenta = 1) THEN f.total ELSE 0 END),2) ventas_externas,
       round(sum(0),2) ventas_externas_no_sujetas_a_proporcionalidad,
       round(sum(0),2) ventas_no_sujetas,
       round(sum(CASE WHEN (fe.exenta = 1) AND (f.ref_client LIKE 'FAC%') THEN 0 ELSE f.total END),2) ventas_gravadas_locales,
       round(sum(0),2) exportaciones_ca,
       round(sum(0),2) exportaciones_fuera_ca,
       round(sum(CASE WHEN (fe.exenta = 0) AND (f.ref_client LIKE 'FEX%') THEN f.total ELSE 0 END),2) exportaciones_servicios,
       round(sum(0),2) ventas_zona_franca,
       round(sum(0),2) ventas_a_cuenta_terceros,
       round(IFNULL(sum(f.total_ttc),0),2) total_ventas,
       2 as numero_anexo
  FROM `llx_facture` f,
       `llx_facture_extrafields` fe
 WHERE f.rowid = fe.fk_object
   AND f.ref_client LIKE 'F%'
   AND f.datef BETWEEN @begin AND @end
GROUP BY 1,2,3,4,5,10,21
ORDER BY 1,2,3,4,5,10,21;
