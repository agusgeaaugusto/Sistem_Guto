
CREATE OR REPLACE VIEW public.vw_venta_cab AS
SELECT v.id_venta, v.fecha, v.total_py AS total_guarani, 
       coalesce(c.id_persona, v.id_cliente) AS id_cliente,
       coalesce(c.nombre_per, v.cliente_nombre) AS cliente_nombre,
       coalesce(c.ruc_ci, v.cliente_ruc) AS cliente_ruc,
       coalesce(c.direccion, v.cliente_direccion) AS cliente_direccion,
       v.timbrado, v.punto_emision, v.nro_factura
FROM public.venta v
LEFT JOIN public.persona c ON c.id_persona = v.id_cliente;

CREATE OR REPLACE VIEW public.vw_venta_det AS
SELECT d.id_venta, d.item, p.id_pro, p.nombre_pro, p.codigo_barra_pro,
       d.cantidad, d.precio_unit, (d.cantidad * d.precio_unit) AS subtotal
FROM public.venta_detalle d
JOIN public.producto p ON p.id_pro = d.id_pro;

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname = 'trg_restar_stock') THEN
    CREATE OR REPLACE FUNCTION public.fn_restar_stock() RETURNS trigger AS $$
    BEGIN
      UPDATE public.producto
         SET cantidad_uni_pro = GREATEST(0, COALESCE(cantidad_uni_pro,0) - NEW.cantidad)
       WHERE id_pro = NEW.id_pro;
      RETURN NEW;
    END; $$ LANGUAGE plpgsql;
    CREATE TRIGGER trg_restar_stock AFTER INSERT ON public.venta_detalle
    FOR EACH ROW EXECUTE FUNCTION public.fn_restar_stock();
  END IF;
END $$;
