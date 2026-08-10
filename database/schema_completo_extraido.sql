ecommerce_pyme_panama
ENCODING
ENCODING
SET client_encoding = 'UTF8';
STDSTRINGS
STDSTRINGS
SET standard_conforming_strings = 'on';
SEARCHPATH
SEARCHPATH
SELECT pg_catalog.set_config('search_path', '', false);
ecommerce_pyme_panama
DATABASE
CREATE DATABASE ecommerce_pyme_panama WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'Spanish_Spain.1252';
DROP DATABASE ecommerce_pyme_panama;
postgres
pgcrypto
EXTENSION
CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;
DROP EXTENSION pgcrypto;
EXTENSION pgcrypto
COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';
actualizar_timestamp()
FUNCTION
CREATE FUNCTION public.actualizar_timestamp() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    NEW.actualizado_en := NOW();
    RETURN NEW;
DROP FUNCTION public.actualizar_timestamp();
postgres
generar_numero_factura()
FUNCTION
CREATE FUNCTION public.generar_numero_factura() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    v_prefijo   VARCHAR;
    v_anio      VARCHAR;
    v_correlativo BIGINT;
    SELECT valor INTO v_prefijo FROM configuracion WHERE clave = 'factura_prefijo';
    v_prefijo   := COALESCE(v_prefijo, 'F');
    v_anio      := TO_CHAR(NOW(), 'YYYY');
    UPDATE configuracion
    SET valor = (COALESCE(valor::BIGINT, 0) + 1)::TEXT,
        actualizado_en = NOW()
    WHERE clave = 'factura_correlativo'
    RETURNING valor::BIGINT INTO v_correlativo;
    NEW.numero := v_prefijo || '-' || v_anio || '-' || LPAD(v_correlativo::TEXT, 4, '0');
    RETURN NEW;
DROP FUNCTION public.generar_numero_factura();
postgres
generar_numero_pedido()
FUNCTION
CREATE FUNCTION public.generar_numero_pedido() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    NEW.numero_pedido := 'P-' || TO_CHAR(NOW(), 'YYYY') || '-' || LPAD(NEW.id::TEXT, 6, '0');
    RETURN NEW;
DROP FUNCTION public.generar_numero_pedido();
postgres
registrar_estado_inicial_pedido()
FUNCTION
CREATE FUNCTION public.registrar_estado_inicial_pedido() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    INSERT INTO estados_pedido (pedido_id, usuario_id, estado, comentario)
    VALUES (NEW.id, NEW.usuario_id, 'pendiente', 'Pedido creado');
    RETURN NEW;
DROP FUNCTION public.registrar_estado_inicial_pedido();
postgres
CREATE TABLE public.brands (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    image bytea,
    image_mime character varying(50),
    image_path character varying(255),
    verified boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
DROP TABLE public.brands;
postgres
brands_id_seq
SEQUENCE
CREATE SEQUENCE public.brands_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.brands_id_seq;
postgres
brands_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.brands_id_seq OWNED BY public.brands.id;
postgres
CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
DROP TABLE public.cache;
postgres
cache_locks
CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
DROP TABLE public.cache_locks;
postgres
carritos
CREATE TABLE public.carritos (
    id bigint NOT NULL,
    usuario_id bigint,
    cupon_id bigint,
    sesion_id character varying(255),
    descuento_aplicado numeric(10,2) DEFAULT 0 NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT carrito_owner CHECK (((usuario_id IS NOT NULL) OR (sesion_id IS NOT NULL)))
DROP TABLE public.carritos;
postgres
TABLE carritos
COMMENT ON TABLE public.carritos IS 'Carritos persistentes: sesi
n para visitantes, usuario_id para logueados';
postgres
carritos_id_seq
SEQUENCE
CREATE SEQUENCE public.carritos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.carritos_id_seq;
postgres
carritos_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.carritos_id_seq OWNED BY public.carritos.id;
postgres
categorias
CREATE TABLE public.categorias (
    id bigint NOT NULL,
    padre_id bigint,
    nombre character varying(150) NOT NULL,
    slug character varying(200) NOT NULL,
    descripcion text,
    imagen_ruta character varying(500),
    activo boolean DEFAULT true NOT NULL,
    orden_visualizacion integer DEFAULT 0 NOT NULL,
    eliminado_en timestamp without time zone,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.categorias;
postgres
TABLE categorias
COMMENT ON TABLE public.categorias IS 'Categor
as y subcategor
as (padre_id self-reference)';
postgres
categorias_id_seq
SEQUENCE
CREATE SEQUENCE public.categorias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.categorias_id_seq;
postgres
categorias_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.categorias_id_seq OWNED BY public.categorias.id;
postgres
configuracion
CREATE TABLE public.configuracion (
    id bigint NOT NULL,
    clave character varying(150) NOT NULL,
    valor text,
    grupo character varying(50) NOT NULL,
    descripcion text,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT configuracion_grupo_check CHECK (((grupo)::text = ANY ((ARRAY['empresa'::character varying, 'pagos'::character varying, 'envios'::character varying, 'impuestos'::character varying, 'correos'::character varying, 'seguridad'::character varying, 'general'::character varying])::text[])))
DROP TABLE public.configuracion;
postgres
TABLE configuracion
COMMENT ON TABLE public.configuracion IS 'Par
metros del sistema en clave-valor agrupados por m
postgres
COLUMN configuracion.valor
COMMENT ON COLUMN public.configuracion.valor IS 'Siempre TEXT. Castear al tipo correcto en la aplicaci
postgres
configuracion_id_seq
SEQUENCE
CREATE SEQUENCE public.configuracion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.configuracion_id_seq;
postgres
configuracion_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.configuracion_id_seq OWNED BY public.configuracion.id;
postgres
CREATE TABLE public.cupones (
    id bigint NOT NULL,
    codigo character varying(50) NOT NULL,
    tipo character varying(30) NOT NULL,
    valor numeric(10,2) NOT NULL,
    monto_minimo numeric(10,2) DEFAULT 0 NOT NULL,
    maximo_usos_total integer,
    usos_por_cliente integer DEFAULT 1 NOT NULL,
    usos_actuales integer DEFAULT 0 NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    inicio_en timestamp without time zone,
    fin_en timestamp without time zone,
    aplica_a character varying(30) DEFAULT 'catalogo'::character varying NOT NULL,
    categoria_id bigint,
    producto_id bigint,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT cupones_aplica_a_check CHECK (((aplica_a)::text = ANY ((ARRAY['catalogo'::character varying, 'categoria'::character varying, 'producto'::character varying])::text[]))),
    CONSTRAINT cupones_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['porcentaje'::character varying, 'monto_fijo'::character varying, 'envio_gratis'::character varying])::text[]))),
    CONSTRAINT cupones_valor_check CHECK ((valor > (0)::numeric))
DROP TABLE public.cupones;
postgres
TABLE cupones
COMMENT ON TABLE public.cupones IS 'Cupones de descuento: porcentaje, monto fijo o env
o gratis';
postgres
cupones_id_seq
SEQUENCE
CREATE SEQUENCE public.cupones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.cupones_id_seq;
postgres
cupones_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.cupones_id_seq OWNED BY public.cupones.id;
postgres
devoluciones
CREATE TABLE public.devoluciones (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    motivo character varying(100) NOT NULL,
    descripcion text NOT NULL,
    foto_evidencia_ruta character varying(500),
    estado character varying(30) DEFAULT 'pendiente'::character varying NOT NULL,
    comentario_admin text,
    aprobado_en timestamp without time zone,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT devoluciones_estado_check CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'aprobada'::character varying, 'rechazada'::character varying])::text[])))
DROP TABLE public.devoluciones;
postgres
TABLE devoluciones
COMMENT ON TABLE public.devoluciones IS 'Solicitudes de devoluci
n iniciadas por el cliente';
postgres
COLUMN devoluciones.foto_evidencia_ruta
COMMENT ON COLUMN public.devoluciones.foto_evidencia_ruta IS 'Ruta relativa: storage/app/public/devoluciones/foto.jpg';
postgres
devoluciones_id_seq
SEQUENCE
CREATE SEQUENCE public.devoluciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.devoluciones_id_seq;
postgres
devoluciones_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.devoluciones_id_seq OWNED BY public.devoluciones.id;
postgres
direcciones
CREATE TABLE public.direcciones (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    alias character varying(50) DEFAULT 'Casa'::character varying NOT NULL,
    nombre_receptor character varying(200) NOT NULL,
    provincia character varying(100) NOT NULL,
    distrito character varying(100) NOT NULL,
    corregimiento character varying(100) NOT NULL,
    direccion_exacta text NOT NULL,
    referencia text,
    es_predeterminada boolean DEFAULT false NOT NULL,
    eliminado_en timestamp without time zone,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.direcciones;
postgres
TABLE direcciones
COMMENT ON TABLE public.direcciones IS 'Direcciones de env
o guardadas por cliente (m
postgres
direcciones_id_seq
SEQUENCE
CREATE SEQUENCE public.direcciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.direcciones_id_seq;
postgres
direcciones_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.direcciones_id_seq OWNED BY public.direcciones.id;
postgres
envios_pedido
CREATE TABLE public.envios_pedido (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    empresa_mensajeria character varying(150),
    numero_guia character varying(150),
    url_rastreo character varying(500),
    fecha_estimada_entrega timestamp without time zone,
    fecha_entrega_real timestamp without time zone,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.envios_pedido;
postgres
TABLE envios_pedido
COMMENT ON TABLE public.envios_pedido IS 'Datos de env
o: mensajer
a y rastreo';
postgres
envios_pedido_id_seq
SEQUENCE
CREATE SEQUENCE public.envios_pedido_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.envios_pedido_id_seq;
postgres
envios_pedido_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.envios_pedido_id_seq OWNED BY public.envios_pedido.id;
postgres
estados_pedido
CREATE TABLE public.estados_pedido (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    usuario_id bigint,
    estado character varying(40) NOT NULL,
    comentario text,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT estados_pedido_estado_check CHECK (((estado)::text = ANY ((ARRAY['pendiente'::character varying, 'pago_confirmado'::character varying, 'pago_rechazado'::character varying, 'en_preparacion'::character varying, 'listo_para_envio'::character varying, 'enviado'::character varying, 'entregado'::character varying, 'cancelado'::character varying, 'devolucion_solicitada'::character varying, 'devolucion_aprobada'::character varying, 'devolucion_rechazada'::character varying])::text[])))
DROP TABLE public.estados_pedido;
postgres
TABLE estados_pedido
COMMENT ON TABLE public.estados_pedido IS 'Historial completo de estados del pedido (tabla aparte)';
postgres
COLUMN estados_pedido.estado
COMMENT ON COLUMN public.estados_pedido.estado IS 'pendiente 
 pago_confirmado 
 en_preparacion 
 listo_para_envio 
 enviado 
 entregado';
postgres
estados_pedido_id_seq
SEQUENCE
CREATE SEQUENCE public.estados_pedido_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.estados_pedido_id_seq;
postgres
estados_pedido_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.estados_pedido_id_seq OWNED BY public.estados_pedido.id;
postgres
facturas
CREATE TABLE public.facturas (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    numero character varying(30) NOT NULL,
    metodo_pago character varying(30),
    referencia_pago_externo character varying(255),
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    descuento numeric(10,2) DEFAULT 0 NOT NULL,
    costo_envio numeric(10,2) DEFAULT 0 NOT NULL,
    itbms_tasa numeric(5,2) DEFAULT 7.00 NOT NULL,
    itbms_monto numeric(10,2) DEFAULT 0 NOT NULL,
    total numeric(10,2) DEFAULT 0 NOT NULL,
    estado character varying(30) DEFAULT 'emitida'::character varying NOT NULL,
    pdf_ruta character varying(500),
    emitida_en timestamp without time zone DEFAULT now() NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT facturas_estado_check CHECK (((estado)::text = ANY ((ARRAY['emitida'::character varying, 'anulada'::character varying])::text[])))
DROP TABLE public.facturas;
postgres
TABLE facturas
COMMENT ON TABLE public.facturas IS 'Facturas generadas autom
ticamente al aprobar pago. PDF en disco';
postgres
COLUMN facturas.pdf_ruta
COMMENT ON COLUMN public.facturas.pdf_ruta IS 'Ruta relativa: storage/app/public/facturas/F-2024-0001.pdf';
postgres
facturas_id_seq
SEQUENCE
CREATE SEQUENCE public.facturas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.facturas_id_seq;
postgres
facturas_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.facturas_id_seq OWNED BY public.facturas.id;
postgres
failed_jobs
CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
DROP TABLE public.failed_jobs;
postgres
failed_jobs_id_seq
SEQUENCE
CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.failed_jobs_id_seq;
postgres
failed_jobs_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;
postgres
imagenes_producto
CREATE TABLE public.imagenes_producto (
    id bigint NOT NULL,
    producto_id bigint NOT NULL,
    ruta character varying(500) NOT NULL,
    es_principal boolean DEFAULT false NOT NULL,
    orden integer DEFAULT 0 NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.imagenes_producto;
postgres
TABLE imagenes_producto
COMMENT ON TABLE public.imagenes_producto IS 'Rutas de im
genes del producto (storage/app/public/products/)';
postgres
COLUMN imagenes_producto.ruta
COMMENT ON COLUMN public.imagenes_producto.ruta IS 'Ruta relativa: storage/app/public/products/imagen.jpg';
postgres
imagenes_producto_id_seq
SEQUENCE
CREATE SEQUENCE public.imagenes_producto_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.imagenes_producto_id_seq;
postgres
imagenes_producto_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.imagenes_producto_id_seq OWNED BY public.imagenes_producto.id;
postgres
items_carrito
CREATE TABLE public.items_carrito (
    id bigint NOT NULL,
    carrito_id bigint NOT NULL,
    producto_id bigint NOT NULL,
    variante_producto_id bigint,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT items_carrito_cantidad_check CHECK ((cantidad > 0)),
    CONSTRAINT items_carrito_precio_unitario_check CHECK ((precio_unitario >= (0)::numeric))
DROP TABLE public.items_carrito;
postgres
TABLE items_carrito
COMMENT ON TABLE public.items_carrito IS 'Productos dentro del carrito con precio congelado';
postgres
items_carrito_id_seq
SEQUENCE
CREATE SEQUENCE public.items_carrito_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.items_carrito_id_seq;
postgres
items_carrito_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.items_carrito_id_seq OWNED BY public.items_carrito.id;
postgres
items_pedido
CREATE TABLE public.items_pedido (
    id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    producto_id bigint NOT NULL,
    variante_producto_id bigint,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT items_pedido_cantidad_check CHECK ((cantidad > 0)),
    CONSTRAINT items_pedido_precio_unitario_check CHECK ((precio_unitario >= (0)::numeric))
DROP TABLE public.items_pedido;
postgres
TABLE items_pedido
COMMENT ON TABLE public.items_pedido IS 'Productos incluidos en cada pedido con precio congelado';
postgres
items_pedido_id_seq
SEQUENCE
CREATE SEQUENCE public.items_pedido_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.items_pedido_id_seq;
postgres
items_pedido_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.items_pedido_id_seq OWNED BY public.items_pedido.id;
postgres
job_batches
CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
DROP TABLE public.job_batches;
postgres
CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
DROP TABLE public.jobs;
postgres
jobs_id_seq
SEQUENCE
CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.jobs_id_seq;
postgres
jobs_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;
postgres
lista_deseos
CREATE TABLE public.lista_deseos (
    usuario_id bigint NOT NULL,
    producto_id bigint NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.lista_deseos;
postgres
TABLE lista_deseos
COMMENT ON TABLE public.lista_deseos IS 'Productos guardados en lista de deseos por usuario';
postgres
logs_auditoria
CREATE TABLE public.logs_auditoria (
    id bigint NOT NULL,
    usuario_id bigint,
    modulo character varying(100) NOT NULL,
    accion character varying(100) NOT NULL,
    descripcion text,
    valor_anterior text,
    valor_nuevo text,
    ip character varying(45),
    agente_usuario character varying(500),
    creado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.logs_auditoria;
postgres
TABLE logs_auditoria
COMMENT ON TABLE public.logs_auditoria IS 'Log inmutable de todas las acciones administrativas';
postgres
logs_auditoria_id_seq
SEQUENCE
CREATE SEQUENCE public.logs_auditoria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.logs_auditoria_id_seq;
postgres
logs_auditoria_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.logs_auditoria_id_seq OWNED BY public.logs_auditoria.id;
postgres
migrations
CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
DROP TABLE public.migrations;
postgres
migrations_id_seq
SEQUENCE
CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.migrations_id_seq;
postgres
migrations_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;
postgres
movimientos_inventario
CREATE TABLE public.movimientos_inventario (
    id bigint NOT NULL,
    producto_id bigint NOT NULL,
    variante_producto_id bigint,
    usuario_id bigint,
    pedido_id bigint,
    tipo character varying(20) NOT NULL,
    cantidad integer NOT NULL,
    stock_antes integer NOT NULL,
    stock_despues integer NOT NULL,
    motivo text NOT NULL,
    proveedor character varying(200),
    factura_proveedor character varying(100),
    notas text,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT movimientos_inventario_tipo_check CHECK (((tipo)::text = ANY ((ARRAY['entrada'::character varying, 'salida'::character varying, 'ajuste'::character varying])::text[])))
DROP TABLE public.movimientos_inventario;
postgres
TABLE movimientos_inventario
COMMENT ON TABLE public.movimientos_inventario IS 'Log de entradas, salidas y ajustes de stock. Solo lectura post-creaci
postgres
movimientos_inventario_id_seq
SEQUENCE
CREATE SEQUENCE public.movimientos_inventario_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.movimientos_inventario_id_seq;
postgres
movimientos_inventario_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.movimientos_inventario_id_seq OWNED BY public.movimientos_inventario.id;
postgres
notificaciones_stock
CREATE TABLE public.notificaciones_stock (
    id bigint NOT NULL,
    producto_id bigint NOT NULL,
    email character varying(255) NOT NULL,
    notificado boolean DEFAULT false NOT NULL,
    notificado_en timestamp(0) without time zone,
    creado_en timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    actualizado_en timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
DROP TABLE public.notificaciones_stock;
postgres
notificaciones_stock_id_seq
SEQUENCE
CREATE SEQUENCE public.notificaciones_stock_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.notificaciones_stock_id_seq;
postgres
notificaciones_stock_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.notificaciones_stock_id_seq OWNED BY public.notificaciones_stock.id;
postgres
opciones_variante
CREATE TABLE public.opciones_variante (
    id bigint NOT NULL,
    tipo_variante_id bigint NOT NULL,
    valor character varying(100) NOT NULL,
    valor_hex character varying(7),
    creado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.opciones_variante;
postgres
TABLE opciones_variante
COMMENT ON TABLE public.opciones_variante IS 'Valores por tipo: Rojo, XL, 128GB, etc.';
postgres
opciones_variante_id_seq
SEQUENCE
CREATE SEQUENCE public.opciones_variante_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.opciones_variante_id_seq;
postgres
opciones_variante_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.opciones_variante_id_seq OWNED BY public.opciones_variante.id;
postgres
password_reset_tokens
CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
DROP TABLE public.password_reset_tokens;
postgres
CREATE TABLE public.pedidos (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    direccion_id bigint,
    cupon_id bigint,
    zona_envio_id bigint,
    numero_pedido character varying(30) NOT NULL,
    metodo_pago character varying(30) NOT NULL,
    subtotal numeric(10,2) DEFAULT 0 NOT NULL,
    descuento numeric(10,2) DEFAULT 0 NOT NULL,
    costo_envio numeric(10,2) DEFAULT 0 NOT NULL,
    itbms_monto numeric(10,2) DEFAULT 0 NOT NULL,
    total numeric(10,2) DEFAULT 0 NOT NULL,
    notas_cliente text,
    notas_internas text,
    comprobante_pago_ruta character varying(500),
    eliminado_en timestamp without time zone,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT pedidos_metodo_pago_check CHECK (((metodo_pago)::text = ANY ((ARRAY['stripe'::character varying, 'yappy'::character varying, 'transferencia'::character varying, 'contra_entrega'::character varying])::text[])))
DROP TABLE public.pedidos;
postgres
TABLE pedidos
COMMENT ON TABLE public.pedidos IS 'Pedidos realizados. El estado actual est
 en estados_pedido';
postgres
COLUMN pedidos.comprobante_pago_ruta
COMMENT ON COLUMN public.pedidos.comprobante_pago_ruta IS 'Ruta relativa: storage/app/public/comprobantes/archivo.jpg';
postgres
pedidos_id_seq
SEQUENCE
CREATE SEQUENCE public.pedidos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.pedidos_id_seq;
postgres
pedidos_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.pedidos_id_seq OWNED BY public.pedidos.id;
postgres
permisos
CREATE TABLE public.permisos (
    id bigint NOT NULL,
    nombre character varying(150) NOT NULL,
    descripcion text,
    modulo character varying(100) NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    name character varying(150) NOT NULL,
    guard_name character varying(125) DEFAULT 'web'::character varying NOT NULL
DROP TABLE public.permisos;
postgres
TABLE permisos
COMMENT ON TABLE public.permisos IS 'Permisos granulares por m
postgres
permisos_id_seq
SEQUENCE
CREATE SEQUENCE public.permisos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.permisos_id_seq;
postgres
permisos_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.permisos_id_seq OWNED BY public.permisos.id;
postgres
producto_del_mes
CREATE TABLE public.producto_del_mes (
    id bigint NOT NULL,
    producto_id bigint NOT NULL,
    descripcion_mes text,
    imagen_banner_ruta character varying(500),
    descuento_especial numeric(5,2),
    inicio_en timestamp without time zone NOT NULL,
    fin_en timestamp without time zone NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT producto_del_mes_descuento_especial_check CHECK (((descuento_especial >= (0)::numeric) AND (descuento_especial <= (100)::numeric)))
DROP TABLE public.producto_del_mes;
postgres
TABLE producto_del_mes
COMMENT ON TABLE public.producto_del_mes IS 'Producto destacado en home (solo 1 activo)';
postgres
producto_del_mes_id_seq
SEQUENCE
CREATE SEQUENCE public.producto_del_mes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.producto_del_mes_id_seq;
postgres
producto_del_mes_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.producto_del_mes_id_seq OWNED BY public.producto_del_mes.id;
postgres
productos
CREATE TABLE public.productos (
    id bigint NOT NULL,
    categoria_id bigint,
    nombre character varying(255) NOT NULL,
    slug character varying(300) NOT NULL,
    descripcion text,
    descripcion_corta character varying(500),
    sku character varying(100) NOT NULL,
    precio numeric(10,2) NOT NULL,
    precio_oferta numeric(10,2),
    oferta_activa boolean DEFAULT false NOT NULL,
    oferta_inicio_en timestamp without time zone,
    oferta_fin_en timestamp without time zone,
    stock integer DEFAULT 0 NOT NULL,
    stock_minimo integer DEFAULT 5 NOT NULL,
    destacado boolean DEFAULT false NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    aplica_itbms boolean DEFAULT true NOT NULL,
    eliminado_en timestamp without time zone,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    marca character varying(100),
    modelo character varying(100),
    marca_logo character varying(255),
    brand_id bigint,
    CONSTRAINT precio_oferta_menor CHECK (((precio_oferta IS NULL) OR (precio_oferta < precio))),
    CONSTRAINT productos_precio_check CHECK ((precio >= (0)::numeric)),
    CONSTRAINT productos_precio_oferta_check CHECK ((precio_oferta >= (0)::numeric)),
    CONSTRAINT productos_stock_check CHECK ((stock >= 0)),
    CONSTRAINT productos_stock_minimo_check CHECK ((stock_minimo >= 0))
DROP TABLE public.productos;
postgres
TABLE productos
COMMENT ON TABLE public.productos IS 'Cat
logo de productos. Im
genes en disco, ruta en BD';
postgres
COLUMN productos.precio_oferta
COMMENT ON COLUMN public.productos.precio_oferta IS 'Si NULL, no hay oferta activa';
postgres
COLUMN productos.oferta_activa
COMMENT ON COLUMN public.productos.oferta_activa IS 'Activado/desactivado por scheduler seg
n oferta_inicio_en/oferta_fin_en';
postgres
productos_id_seq
SEQUENCE
CREATE SEQUENCE public.productos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.productos_id_seq;
postgres
productos_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.productos_id_seq OWNED BY public.productos.id;
postgres
promociones_envio_gratis
CREATE TABLE public.promociones_envio_gratis (
    id bigint NOT NULL,
    zona_envio_id bigint,
    monto_minimo numeric(10,2) DEFAULT 0 NOT NULL,
    inicio_en timestamp without time zone NOT NULL,
    fin_en timestamp without time zone NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.promociones_envio_gratis;
postgres
TABLE promociones_envio_gratis
COMMENT ON TABLE public.promociones_envio_gratis IS 'Promociones de env
o gratuito por zona y monto m
postgres
promociones_envio_gratis_id_seq
SEQUENCE
CREATE SEQUENCE public.promociones_envio_gratis_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.promociones_envio_gratis_id_seq;
postgres
promociones_envio_gratis_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.promociones_envio_gratis_id_seq OWNED BY public.promociones_envio_gratis.id;
postgres
reenvios_factura
CREATE TABLE public.reenvios_factura (
    id bigint NOT NULL,
    factura_id bigint NOT NULL,
    usuario_id bigint,
    email_destino character varying(255) NOT NULL,
    mensaje_personalizado text,
    enviado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.reenvios_factura;
postgres
TABLE reenvios_factura
COMMENT ON TABLE public.reenvios_factura IS 'Registro de cada vez que se reenvi
 una factura por email';
postgres
reenvios_factura_id_seq
SEQUENCE
CREATE SEQUENCE public.reenvios_factura_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.reenvios_factura_id_seq;
postgres
reenvios_factura_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.reenvios_factura_id_seq OWNED BY public.reenvios_factura.id;
postgres
rol_permisos
CREATE TABLE public.rol_permisos (
    rol_id bigint NOT NULL,
    permiso_id bigint NOT NULL
DROP TABLE public.rol_permisos;
postgres
TABLE rol_permisos
COMMENT ON TABLE public.rol_permisos IS 'Relaci
n muchos a muchos: rol 
 permiso';
postgres
CREATE TABLE public.roles (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    descripcion text,
    activo boolean DEFAULT true NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    name character varying(125) NOT NULL,
    guard_name character varying(125) DEFAULT 'web'::character varying NOT NULL
DROP TABLE public.roles;
postgres
TABLE roles
COMMENT ON TABLE public.roles IS 'Roles de acceso. Integrado con Spatie Permission';
postgres
roles_id_seq
SEQUENCE
CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.roles_id_seq;
postgres
roles_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;
postgres
sessions
CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
DROP TABLE public.sessions;
postgres
tipos_variante
CREATE TABLE public.tipos_variante (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.tipos_variante;
postgres
TABLE tipos_variante
COMMENT ON TABLE public.tipos_variante IS 'Tipos de variante: Color, Talla, Capacidad, etc.';
postgres
tipos_variante_id_seq
SEQUENCE
CREATE SEQUENCE public.tipos_variante_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.tipos_variante_id_seq;
postgres
tipos_variante_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.tipos_variante_id_seq OWNED BY public.tipos_variante.id;
postgres
CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
DROP TABLE public.users;
postgres
users_id_seq
SEQUENCE
CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.users_id_seq;
postgres
users_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;
postgres
usos_cupon
CREATE TABLE public.usos_cupon (
    id bigint NOT NULL,
    cupon_id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    pedido_id bigint NOT NULL,
    descuento_aplicado numeric(10,2) DEFAULT 0 NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.usos_cupon;
postgres
TABLE usos_cupon
COMMENT ON TABLE public.usos_cupon IS 'Trazabilidad de uso de cupones por pedido y usuario';
postgres
usos_cupon_id_seq
SEQUENCE
CREATE SEQUENCE public.usos_cupon_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.usos_cupon_id_seq;
postgres
usos_cupon_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.usos_cupon_id_seq OWNED BY public.usos_cupon.id;
postgres
usuario_permisos
CREATE TABLE public.usuario_permisos (
    permiso_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    usuario_id bigint NOT NULL
DROP TABLE public.usuario_permisos;
postgres
usuario_roles
CREATE TABLE public.usuario_roles (
    usuario_id bigint NOT NULL,
    rol_id bigint NOT NULL,
    asignado_en timestamp without time zone DEFAULT now() NOT NULL,
    model_type character varying(255)
DROP TABLE public.usuario_roles;
postgres
TABLE usuario_roles
COMMENT ON TABLE public.usuario_roles IS 'Relaci
n muchos a muchos: usuario 
postgres
usuarios
CREATE TABLE public.usuarios (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    apellido character varying(100) NOT NULL,
    email character varying(255) NOT NULL,
    password_hash character varying(255) NOT NULL,
    telefono character varying(30),
    foto_perfil_ruta character varying(500),
    fecha_nacimiento date,
    activo boolean DEFAULT true NOT NULL,
    bloqueado boolean DEFAULT false NOT NULL,
    motivo_bloqueo text,
    bloqueado_en timestamp without time zone,
    two_fa_habilitado boolean DEFAULT false NOT NULL,
    two_fa_secreto character varying(255),
    remember_token character varying(100),
    email_verificado_en timestamp without time zone,
    ultimo_login_en timestamp without time zone,
    ultimo_login_ip character varying(45),
    eliminado_en timestamp without time zone,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL
DROP TABLE public.usuarios;
postgres
TABLE usuarios
COMMENT ON TABLE public.usuarios IS 'Todos los usuarios del sistema: administradores y clientes';
postgres
COLUMN usuarios.foto_perfil_ruta
COMMENT ON COLUMN public.usuarios.foto_perfil_ruta IS 'Ruta relativa: storage/app/public/perfiles/foto.jpg';
postgres
usuarios_id_seq
SEQUENCE
CREATE SEQUENCE public.usuarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.usuarios_id_seq;
postgres
usuarios_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;
postgres
variante_opciones
CREATE TABLE public.variante_opciones (
    variante_producto_id bigint NOT NULL,
    opcion_variante_id bigint NOT NULL
DROP TABLE public.variante_opciones;
postgres
TABLE variante_opciones
COMMENT ON TABLE public.variante_opciones IS 'Pivot: qu
 opciones conforman cada variante';
postgres
variantes_producto
CREATE TABLE public.variantes_producto (
    id bigint NOT NULL,
    producto_id bigint NOT NULL,
    sku character varying(100) NOT NULL,
    precio numeric(10,2),
    stock integer DEFAULT 0 NOT NULL,
    imagen_ruta character varying(500),
    activo boolean DEFAULT true NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT variantes_producto_precio_check CHECK ((precio >= (0)::numeric)),
    CONSTRAINT variantes_producto_stock_check CHECK ((stock >= 0))
DROP TABLE public.variantes_producto;
postgres
TABLE variantes_producto
COMMENT ON TABLE public.variantes_producto IS 'Combinaci
n de opciones con stock y precio propio';
postgres
variantes_producto_id_seq
SEQUENCE
CREATE SEQUENCE public.variantes_producto_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.variantes_producto_id_seq;
postgres
variantes_producto_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.variantes_producto_id_seq OWNED BY public.variantes_producto.id;
postgres
zonas_envio
CREATE TABLE public.zonas_envio (
    id bigint NOT NULL,
    nombre character varying(150) NOT NULL,
    provincias text,
    costo numeric(10,2) DEFAULT 0 NOT NULL,
    tiempo_estimado character varying(100),
    activo boolean DEFAULT true NOT NULL,
    creado_en timestamp without time zone DEFAULT now() NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT zonas_envio_costo_check CHECK ((costo >= (0)::numeric))
DROP TABLE public.zonas_envio;
postgres
TABLE zonas_envio
COMMENT ON TABLE public.zonas_envio IS 'Zonas geogr
ficas de entrega con costo y tiempo';
postgres
zonas_envio_id_seq
SEQUENCE
CREATE SEQUENCE public.zonas_envio_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
DROP SEQUENCE public.zonas_envio_id_seq;
postgres
zonas_envio_id_seq
SEQUENCE OWNED BY
ALTER SEQUENCE public.zonas_envio_id_seq OWNED BY public.zonas_envio.id;
postgres
brands id
ALTER TABLE ONLY public.brands ALTER COLUMN id SET DEFAULT nextval('public.brands_id_seq'::regclass);
ALTER TABLE public.brands ALTER COLUMN id DROP DEFAULT;
postgres
carritos id
ALTER TABLE ONLY public.carritos ALTER COLUMN id SET DEFAULT nextval('public.carritos_id_seq'::regclass);
ALTER TABLE public.carritos ALTER COLUMN id DROP DEFAULT;
postgres
categorias id
ALTER TABLE ONLY public.categorias ALTER COLUMN id SET DEFAULT nextval('public.categorias_id_seq'::regclass);
ALTER TABLE public.categorias ALTER COLUMN id DROP DEFAULT;
postgres
configuracion id
ALTER TABLE ONLY public.configuracion ALTER COLUMN id SET DEFAULT nextval('public.configuracion_id_seq'::regclass);
ALTER TABLE public.configuracion ALTER COLUMN id DROP DEFAULT;
postgres
cupones id
ALTER TABLE ONLY public.cupones ALTER COLUMN id SET DEFAULT nextval('public.cupones_id_seq'::regclass);
ALTER TABLE public.cupones ALTER COLUMN id DROP DEFAULT;
postgres
devoluciones id
ALTER TABLE ONLY public.devoluciones ALTER COLUMN id SET DEFAULT nextval('public.devoluciones_id_seq'::regclass);
ALTER TABLE public.devoluciones ALTER COLUMN id DROP DEFAULT;
postgres
direcciones id
ALTER TABLE ONLY public.direcciones ALTER COLUMN id SET DEFAULT nextval('public.direcciones_id_seq'::regclass);
ALTER TABLE public.direcciones ALTER COLUMN id DROP DEFAULT;
postgres
envios_pedido id
ALTER TABLE ONLY public.envios_pedido ALTER COLUMN id SET DEFAULT nextval('public.envios_pedido_id_seq'::regclass);
ALTER TABLE public.envios_pedido ALTER COLUMN id DROP DEFAULT;
postgres
estados_pedido id
ALTER TABLE ONLY public.estados_pedido ALTER COLUMN id SET DEFAULT nextval('public.estados_pedido_id_seq'::regclass);
ALTER TABLE public.estados_pedido ALTER COLUMN id DROP DEFAULT;
postgres
facturas id
ALTER TABLE ONLY public.facturas ALTER COLUMN id SET DEFAULT nextval('public.facturas_id_seq'::regclass);
ALTER TABLE public.facturas ALTER COLUMN id DROP DEFAULT;
postgres
failed_jobs id
ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);
ALTER TABLE public.failed_jobs ALTER COLUMN id DROP DEFAULT;
postgres
imagenes_producto id
ALTER TABLE ONLY public.imagenes_producto ALTER COLUMN id SET DEFAULT nextval('public.imagenes_producto_id_seq'::regclass);
ALTER TABLE public.imagenes_producto ALTER COLUMN id DROP DEFAULT;
postgres
items_carrito id
ALTER TABLE ONLY public.items_carrito ALTER COLUMN id SET DEFAULT nextval('public.items_carrito_id_seq'::regclass);
ALTER TABLE public.items_carrito ALTER COLUMN id DROP DEFAULT;
postgres
items_pedido id
ALTER TABLE ONLY public.items_pedido ALTER COLUMN id SET DEFAULT nextval('public.items_pedido_id_seq'::regclass);
ALTER TABLE public.items_pedido ALTER COLUMN id DROP DEFAULT;
postgres
ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);
ALTER TABLE public.jobs ALTER COLUMN id DROP DEFAULT;
postgres
logs_auditoria id
ALTER TABLE ONLY public.logs_auditoria ALTER COLUMN id SET DEFAULT nextval('public.logs_auditoria_id_seq'::regclass);
ALTER TABLE public.logs_auditoria ALTER COLUMN id DROP DEFAULT;
postgres
migrations id
ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);
ALTER TABLE public.migrations ALTER COLUMN id DROP DEFAULT;
postgres
movimientos_inventario id
ALTER TABLE ONLY public.movimientos_inventario ALTER COLUMN id SET DEFAULT nextval('public.movimientos_inventario_id_seq'::regclass);
ALTER TABLE public.movimientos_inventario ALTER COLUMN id DROP DEFAULT;
postgres
notificaciones_stock id
ALTER TABLE ONLY public.notificaciones_stock ALTER COLUMN id SET DEFAULT nextval('public.notificaciones_stock_id_seq'::regclass);
ALTER TABLE public.notificaciones_stock ALTER COLUMN id DROP DEFAULT;
postgres
opciones_variante id
ALTER TABLE ONLY public.opciones_variante ALTER COLUMN id SET DEFAULT nextval('public.opciones_variante_id_seq'::regclass);
ALTER TABLE public.opciones_variante ALTER COLUMN id DROP DEFAULT;
postgres
pedidos id
ALTER TABLE ONLY public.pedidos ALTER COLUMN id SET DEFAULT nextval('public.pedidos_id_seq'::regclass);
ALTER TABLE public.pedidos ALTER COLUMN id DROP DEFAULT;
postgres
permisos id
ALTER TABLE ONLY public.permisos ALTER COLUMN id SET DEFAULT nextval('public.permisos_id_seq'::regclass);
ALTER TABLE public.permisos ALTER COLUMN id DROP DEFAULT;
postgres
producto_del_mes id
ALTER TABLE ONLY public.producto_del_mes ALTER COLUMN id SET DEFAULT nextval('public.producto_del_mes_id_seq'::regclass);
ALTER TABLE public.producto_del_mes ALTER COLUMN id DROP DEFAULT;
postgres
productos id
ALTER TABLE ONLY public.productos ALTER COLUMN id SET DEFAULT nextval('public.productos_id_seq'::regclass);
ALTER TABLE public.productos ALTER COLUMN id DROP DEFAULT;
postgres
promociones_envio_gratis id
ALTER TABLE ONLY public.promociones_envio_gratis ALTER COLUMN id SET DEFAULT nextval('public.promociones_envio_gratis_id_seq'::regclass);
ALTER TABLE public.promociones_envio_gratis ALTER COLUMN id DROP DEFAULT;
postgres
reenvios_factura id
ALTER TABLE ONLY public.reenvios_factura ALTER COLUMN id SET DEFAULT nextval('public.reenvios_factura_id_seq'::regclass);
ALTER TABLE public.reenvios_factura ALTER COLUMN id DROP DEFAULT;
postgres
roles id
ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);
ALTER TABLE public.roles ALTER COLUMN id DROP DEFAULT;
postgres
tipos_variante id
ALTER TABLE ONLY public.tipos_variante ALTER COLUMN id SET DEFAULT nextval('public.tipos_variante_id_seq'::regclass);
ALTER TABLE public.tipos_variante ALTER COLUMN id DROP DEFAULT;
postgres
users id
ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);
ALTER TABLE public.users ALTER COLUMN id DROP DEFAULT;
postgres
usos_cupon id
ALTER TABLE ONLY public.usos_cupon ALTER COLUMN id SET DEFAULT nextval('public.usos_cupon_id_seq'::regclass);
ALTER TABLE public.usos_cupon ALTER COLUMN id DROP DEFAULT;
postgres
usuarios id
ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);
ALTER TABLE public.usuarios ALTER COLUMN id DROP DEFAULT;
postgres
variantes_producto id
ALTER TABLE ONLY public.variantes_producto ALTER COLUMN id SET DEFAULT nextval('public.variantes_producto_id_seq'::regclass);
ALTER TABLE public.variantes_producto ALTER COLUMN id DROP DEFAULT;
postgres
zonas_envio id
ALTER TABLE ONLY public.zonas_envio ALTER COLUMN id SET DEFAULT nextval('public.zonas_envio_id_seq'::regclass);
ALTER TABLE public.zonas_envio ALTER COLUMN id DROP DEFAULT;
postgres
TABLE DATA
SELECT pg_catalog.setval('public.movimientos_inventario_id_seq', 1, false);
postgres
notificaciones_stock_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.notificaciones_stock_id_seq', 2, true);
postgres
opciones_variante_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.opciones_variante_id_seq', 261, true);
postgres
pedidos_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.pedidos_id_seq', 10, true);
postgres
permisos_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.permisos_id_seq', 29, true);
postgres
producto_del_mes_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.producto_del_mes_id_seq', 5, true);
postgres
productos_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.productos_id_seq', 9, true);
postgres
promociones_envio_gratis_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.promociones_envio_gratis_id_seq', 3, true);
postgres
reenvios_factura_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.reenvios_factura_id_seq', 1, false);
postgres
roles_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.roles_id_seq', 5, true);
postgres
tipos_variante_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.tipos_variante_id_seq', 42, true);
postgres
users_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.users_id_seq', 2, true);
postgres
usos_cupon_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.usos_cupon_id_seq', 1, false);
postgres
usuarios_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.usuarios_id_seq', 59, true);
postgres
variantes_producto_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.variantes_producto_id_seq', 45, true);
postgres
zonas_envio_id_seq
SEQUENCE SET
SELECT pg_catalog.setval('public.zonas_envio_id_seq', 13, true);
postgres
brands brands_pkey
CONSTRAINT
ALTER TABLE ONLY public.brands
    ADD CONSTRAINT brands_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.brands DROP CONSTRAINT brands_pkey;
postgres
brands brands_slug_unique
CONSTRAINT
ALTER TABLE ONLY public.brands
    ADD CONSTRAINT brands_slug_unique UNIQUE (slug);
ALTER TABLE ONLY public.brands DROP CONSTRAINT brands_slug_unique;
postgres
cache_locks cache_locks_pkey
CONSTRAINT
ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);
ALTER TABLE ONLY public.cache_locks DROP CONSTRAINT cache_locks_pkey;
postgres
cache cache_pkey
CONSTRAINT
ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);
ALTER TABLE ONLY public.cache DROP CONSTRAINT cache_pkey;
postgres
carritos carritos_pkey
CONSTRAINT
ALTER TABLE ONLY public.carritos
    ADD CONSTRAINT carritos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.carritos DROP CONSTRAINT carritos_pkey;
postgres
categorias categorias_pkey
CONSTRAINT
ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.categorias DROP CONSTRAINT categorias_pkey;
postgres
categorias categorias_slug_key
CONSTRAINT
ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_slug_key UNIQUE (slug);
ALTER TABLE ONLY public.categorias DROP CONSTRAINT categorias_slug_key;
postgres
configuracion configuracion_clave_key
CONSTRAINT
ALTER TABLE ONLY public.configuracion
    ADD CONSTRAINT configuracion_clave_key UNIQUE (clave);
ALTER TABLE ONLY public.configuracion DROP CONSTRAINT configuracion_clave_key;
postgres
configuracion configuracion_pkey
CONSTRAINT
ALTER TABLE ONLY public.configuracion
    ADD CONSTRAINT configuracion_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.configuracion DROP CONSTRAINT configuracion_pkey;
postgres
cupones cupones_codigo_key
CONSTRAINT
ALTER TABLE ONLY public.cupones
    ADD CONSTRAINT cupones_codigo_key UNIQUE (codigo);
ALTER TABLE ONLY public.cupones DROP CONSTRAINT cupones_codigo_key;
postgres
cupones cupones_pkey
CONSTRAINT
ALTER TABLE ONLY public.cupones
    ADD CONSTRAINT cupones_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.cupones DROP CONSTRAINT cupones_pkey;
postgres
devoluciones devoluciones_pkey
CONSTRAINT
ALTER TABLE ONLY public.devoluciones
    ADD CONSTRAINT devoluciones_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.devoluciones DROP CONSTRAINT devoluciones_pkey;
postgres
direcciones direcciones_pkey
CONSTRAINT
ALTER TABLE ONLY public.direcciones
    ADD CONSTRAINT direcciones_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.direcciones DROP CONSTRAINT direcciones_pkey;
postgres
envios_pedido envios_pedido_pedido_id_key
CONSTRAINT
ALTER TABLE ONLY public.envios_pedido
    ADD CONSTRAINT envios_pedido_pedido_id_key UNIQUE (pedido_id);
ALTER TABLE ONLY public.envios_pedido DROP CONSTRAINT envios_pedido_pedido_id_key;
postgres
envios_pedido envios_pedido_pkey
CONSTRAINT
ALTER TABLE ONLY public.envios_pedido
    ADD CONSTRAINT envios_pedido_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.envios_pedido DROP CONSTRAINT envios_pedido_pkey;
postgres
estados_pedido estados_pedido_pkey
CONSTRAINT
ALTER TABLE ONLY public.estados_pedido
    ADD CONSTRAINT estados_pedido_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.estados_pedido DROP CONSTRAINT estados_pedido_pkey;
postgres
facturas facturas_numero_key
CONSTRAINT
ALTER TABLE ONLY public.facturas
    ADD CONSTRAINT facturas_numero_key UNIQUE (numero);
ALTER TABLE ONLY public.facturas DROP CONSTRAINT facturas_numero_key;
postgres
facturas facturas_pedido_id_key
CONSTRAINT
ALTER TABLE ONLY public.facturas
    ADD CONSTRAINT facturas_pedido_id_key UNIQUE (pedido_id);
ALTER TABLE ONLY public.facturas DROP CONSTRAINT facturas_pedido_id_key;
postgres
facturas facturas_pkey
CONSTRAINT
ALTER TABLE ONLY public.facturas
    ADD CONSTRAINT facturas_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.facturas DROP CONSTRAINT facturas_pkey;
postgres
failed_jobs failed_jobs_pkey
CONSTRAINT
ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.failed_jobs DROP CONSTRAINT failed_jobs_pkey;
postgres
failed_jobs failed_jobs_uuid_unique
CONSTRAINT
ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);
ALTER TABLE ONLY public.failed_jobs DROP CONSTRAINT failed_jobs_uuid_unique;
postgres
imagenes_producto imagenes_producto_pkey
CONSTRAINT
ALTER TABLE ONLY public.imagenes_producto
    ADD CONSTRAINT imagenes_producto_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.imagenes_producto DROP CONSTRAINT imagenes_producto_pkey;
postgres
items_carrito items_carrito_carrito_id_producto_id_variante_producto_id_key
CONSTRAINT
ALTER TABLE ONLY public.items_carrito
    ADD CONSTRAINT items_carrito_carrito_id_producto_id_variante_producto_id_key UNIQUE (carrito_id, producto_id, variante_producto_id);
ALTER TABLE ONLY public.items_carrito DROP CONSTRAINT items_carrito_carrito_id_producto_id_variante_producto_id_key;
postgres
items_carrito items_carrito_pkey
CONSTRAINT
ALTER TABLE ONLY public.items_carrito
    ADD CONSTRAINT items_carrito_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.items_carrito DROP CONSTRAINT items_carrito_pkey;
postgres
items_pedido items_pedido_pkey
CONSTRAINT
ALTER TABLE ONLY public.items_pedido
    ADD CONSTRAINT items_pedido_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.items_pedido DROP CONSTRAINT items_pedido_pkey;
postgres
job_batches job_batches_pkey
CONSTRAINT
ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.job_batches DROP CONSTRAINT job_batches_pkey;
postgres
jobs jobs_pkey
CONSTRAINT
ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.jobs DROP CONSTRAINT jobs_pkey;
postgres
lista_deseos lista_deseos_pkey
CONSTRAINT
ALTER TABLE ONLY public.lista_deseos
    ADD CONSTRAINT lista_deseos_pkey PRIMARY KEY (usuario_id, producto_id);
ALTER TABLE ONLY public.lista_deseos DROP CONSTRAINT lista_deseos_pkey;
postgres
logs_auditoria logs_auditoria_pkey
CONSTRAINT
ALTER TABLE ONLY public.logs_auditoria
    ADD CONSTRAINT logs_auditoria_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.logs_auditoria DROP CONSTRAINT logs_auditoria_pkey;
postgres
migrations migrations_pkey
CONSTRAINT
ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.migrations DROP CONSTRAINT migrations_pkey;
postgres
movimientos_inventario movimientos_inventario_pkey
CONSTRAINT
ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.movimientos_inventario DROP CONSTRAINT movimientos_inventario_pkey;
postgres
notificaciones_stock notificaciones_stock_pkey
CONSTRAINT
ALTER TABLE ONLY public.notificaciones_stock
    ADD CONSTRAINT notificaciones_stock_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.notificaciones_stock DROP CONSTRAINT notificaciones_stock_pkey;
postgres
opciones_variante opciones_variante_pkey
CONSTRAINT
ALTER TABLE ONLY public.opciones_variante
    ADD CONSTRAINT opciones_variante_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.opciones_variante DROP CONSTRAINT opciones_variante_pkey;
postgres
opciones_variante opciones_variante_tipo_variante_id_valor_key
CONSTRAINT
ALTER TABLE ONLY public.opciones_variante
    ADD CONSTRAINT opciones_variante_tipo_variante_id_valor_key UNIQUE (tipo_variante_id, valor);
ALTER TABLE ONLY public.opciones_variante DROP CONSTRAINT opciones_variante_tipo_variante_id_valor_key;
postgres
password_reset_tokens password_reset_tokens_pkey
CONSTRAINT
ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);
ALTER TABLE ONLY public.password_reset_tokens DROP CONSTRAINT password_reset_tokens_pkey;
postgres
pedidos pedidos_numero_pedido_key
CONSTRAINT
ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_numero_pedido_key UNIQUE (numero_pedido);
ALTER TABLE ONLY public.pedidos DROP CONSTRAINT pedidos_numero_pedido_key;
postgres
pedidos pedidos_pkey
CONSTRAINT
ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.pedidos DROP CONSTRAINT pedidos_pkey;
postgres
permisos permisos_name_guard_name_unique
CONSTRAINT
ALTER TABLE ONLY public.permisos
    ADD CONSTRAINT permisos_name_guard_name_unique UNIQUE (name, guard_name);
ALTER TABLE ONLY public.permisos DROP CONSTRAINT permisos_name_guard_name_unique;
postgres
permisos permisos_nombre_key
CONSTRAINT
ALTER TABLE ONLY public.permisos
    ADD CONSTRAINT permisos_nombre_key UNIQUE (nombre);
ALTER TABLE ONLY public.permisos DROP CONSTRAINT permisos_nombre_key;
postgres
permisos permisos_pkey
CONSTRAINT
ALTER TABLE ONLY public.permisos
    ADD CONSTRAINT permisos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.permisos DROP CONSTRAINT permisos_pkey;
postgres
producto_del_mes producto_del_mes_pkey
CONSTRAINT
ALTER TABLE ONLY public.producto_del_mes
    ADD CONSTRAINT producto_del_mes_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.producto_del_mes DROP CONSTRAINT producto_del_mes_pkey;
postgres
productos productos_pkey
CONSTRAINT
ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.productos DROP CONSTRAINT productos_pkey;
postgres
productos productos_sku_key
CONSTRAINT
ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_sku_key UNIQUE (sku);
ALTER TABLE ONLY public.productos DROP CONSTRAINT productos_sku_key;
postgres
productos productos_slug_key
CONSTRAINT
ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_slug_key UNIQUE (slug);
ALTER TABLE ONLY public.productos DROP CONSTRAINT productos_slug_key;
postgres
promociones_envio_gratis promociones_envio_gratis_pkey
CONSTRAINT
ALTER TABLE ONLY public.promociones_envio_gratis
    ADD CONSTRAINT promociones_envio_gratis_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.promociones_envio_gratis DROP CONSTRAINT promociones_envio_gratis_pkey;
postgres
reenvios_factura reenvios_factura_pkey
CONSTRAINT
ALTER TABLE ONLY public.reenvios_factura
    ADD CONSTRAINT reenvios_factura_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.reenvios_factura DROP CONSTRAINT reenvios_factura_pkey;
postgres
rol_permisos rol_permisos_pkey
CONSTRAINT
ALTER TABLE ONLY public.rol_permisos
    ADD CONSTRAINT rol_permisos_pkey PRIMARY KEY (rol_id, permiso_id);
ALTER TABLE ONLY public.rol_permisos DROP CONSTRAINT rol_permisos_pkey;
postgres
roles roles_name_guard_name_unique
CONSTRAINT
ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);
ALTER TABLE ONLY public.roles DROP CONSTRAINT roles_name_guard_name_unique;
postgres
roles roles_nombre_key
CONSTRAINT
ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_nombre_key UNIQUE (nombre);
ALTER TABLE ONLY public.roles DROP CONSTRAINT roles_nombre_key;
postgres
roles roles_pkey
CONSTRAINT
ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.roles DROP CONSTRAINT roles_pkey;
postgres
sessions sessions_pkey
CONSTRAINT
ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.sessions DROP CONSTRAINT sessions_pkey;
postgres
tipos_variante tipos_variante_nombre_key
CONSTRAINT
ALTER TABLE ONLY public.tipos_variante
    ADD CONSTRAINT tipos_variante_nombre_key UNIQUE (nombre);
ALTER TABLE ONLY public.tipos_variante DROP CONSTRAINT tipos_variante_nombre_key;
postgres
tipos_variante tipos_variante_pkey
CONSTRAINT
ALTER TABLE ONLY public.tipos_variante
    ADD CONSTRAINT tipos_variante_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.tipos_variante DROP CONSTRAINT tipos_variante_pkey;
postgres
users users_email_unique
CONSTRAINT
ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);
ALTER TABLE ONLY public.users DROP CONSTRAINT users_email_unique;
postgres
users users_pkey
CONSTRAINT
ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.users DROP CONSTRAINT users_pkey;
postgres
usos_cupon usos_cupon_cupon_id_pedido_id_key
CONSTRAINT
ALTER TABLE ONLY public.usos_cupon
    ADD CONSTRAINT usos_cupon_cupon_id_pedido_id_key UNIQUE (cupon_id, pedido_id);
ALTER TABLE ONLY public.usos_cupon DROP CONSTRAINT usos_cupon_cupon_id_pedido_id_key;
postgres
usos_cupon usos_cupon_pkey
CONSTRAINT
ALTER TABLE ONLY public.usos_cupon
    ADD CONSTRAINT usos_cupon_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.usos_cupon DROP CONSTRAINT usos_cupon_pkey;
postgres
usuario_permisos usuario_permisos_pkey
CONSTRAINT
ALTER TABLE ONLY public.usuario_permisos
    ADD CONSTRAINT usuario_permisos_pkey PRIMARY KEY (permiso_id, usuario_id, model_type);
ALTER TABLE ONLY public.usuario_permisos DROP CONSTRAINT usuario_permisos_pkey;
postgres
usuario_roles usuario_roles_pkey
CONSTRAINT
ALTER TABLE ONLY public.usuario_roles
    ADD CONSTRAINT usuario_roles_pkey PRIMARY KEY (usuario_id, rol_id);
ALTER TABLE ONLY public.usuario_roles DROP CONSTRAINT usuario_roles_pkey;
postgres
usuarios usuarios_email_key
CONSTRAINT
ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_email_key UNIQUE (email);
ALTER TABLE ONLY public.usuarios DROP CONSTRAINT usuarios_email_key;
postgres
usuarios usuarios_pkey
CONSTRAINT
ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.usuarios DROP CONSTRAINT usuarios_pkey;
postgres
variante_opciones variante_opciones_pkey
CONSTRAINT
ALTER TABLE ONLY public.variante_opciones
    ADD CONSTRAINT variante_opciones_pkey PRIMARY KEY (variante_producto_id, opcion_variante_id);
ALTER TABLE ONLY public.variante_opciones DROP CONSTRAINT variante_opciones_pkey;
postgres
variantes_producto variantes_producto_pkey
CONSTRAINT
ALTER TABLE ONLY public.variantes_producto
    ADD CONSTRAINT variantes_producto_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.variantes_producto DROP CONSTRAINT variantes_producto_pkey;
postgres
variantes_producto variantes_producto_sku_key
CONSTRAINT
ALTER TABLE ONLY public.variantes_producto
    ADD CONSTRAINT variantes_producto_sku_key UNIQUE (sku);
ALTER TABLE ONLY public.variantes_producto DROP CONSTRAINT variantes_producto_sku_key;
postgres
zonas_envio zonas_envio_pkey
CONSTRAINT
ALTER TABLE ONLY public.zonas_envio
    ADD CONSTRAINT zonas_envio_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.zonas_envio DROP CONSTRAINT zonas_envio_pkey;
postgres
cache_expiration_index
CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);
DROP INDEX public.cache_expiration_index;
postgres
cache_locks_expiration_index
CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);
DROP INDEX public.cache_locks_expiration_index;
postgres
failed_jobs_connection_queue_failed_at_index
CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);
DROP INDEX public.failed_jobs_connection_queue_failed_at_index;
postgres
idx_carritos_sesion
CREATE INDEX idx_carritos_sesion ON public.carritos USING btree (sesion_id);
DROP INDEX public.idx_carritos_sesion;
postgres
idx_carritos_usuario
CREATE INDEX idx_carritos_usuario ON public.carritos USING btree (usuario_id);
DROP INDEX public.idx_carritos_usuario;
postgres
idx_categorias_activo
CREATE INDEX idx_categorias_activo ON public.categorias USING btree (activo) WHERE (eliminado_en IS NULL);
DROP INDEX public.idx_categorias_activo;
postgres
idx_categorias_padre
CREATE INDEX idx_categorias_padre ON public.categorias USING btree (padre_id);
DROP INDEX public.idx_categorias_padre;
postgres
idx_categorias_slug
CREATE INDEX idx_categorias_slug ON public.categorias USING btree (slug);
DROP INDEX public.idx_categorias_slug;
postgres
idx_cupones_activo
CREATE INDEX idx_cupones_activo ON public.cupones USING btree (activo);
DROP INDEX public.idx_cupones_activo;
postgres
idx_cupones_codigo
CREATE INDEX idx_cupones_codigo ON public.cupones USING btree (codigo);
DROP INDEX public.idx_cupones_codigo;
postgres
idx_direcciones_usuario
CREATE INDEX idx_direcciones_usuario ON public.direcciones USING btree (usuario_id) WHERE (eliminado_en IS NULL);
DROP INDEX public.idx_direcciones_usuario;
postgres
idx_estados_pedido_estado
CREATE INDEX idx_estados_pedido_estado ON public.estados_pedido USING btree (estado);
DROP INDEX public.idx_estados_pedido_estado;
postgres
idx_estados_pedido_pedido
CREATE INDEX idx_estados_pedido_pedido ON public.estados_pedido USING btree (pedido_id);
DROP INDEX public.idx_estados_pedido_pedido;
postgres
idx_facturas_emitida
CREATE INDEX idx_facturas_emitida ON public.facturas USING btree (emitida_en DESC);
DROP INDEX public.idx_facturas_emitida;
postgres
idx_facturas_numero
CREATE INDEX idx_facturas_numero ON public.facturas USING btree (numero);
DROP INDEX public.idx_facturas_numero;
postgres
idx_facturas_pedido
CREATE INDEX idx_facturas_pedido ON public.facturas USING btree (pedido_id);
DROP INDEX public.idx_facturas_pedido;
postgres
idx_facturas_usuario
CREATE INDEX idx_facturas_usuario ON public.facturas USING btree (usuario_id);
DROP INDEX public.idx_facturas_usuario;
postgres
idx_imagenes_principal
CREATE INDEX idx_imagenes_principal ON public.imagenes_producto USING btree (producto_id, es_principal);
DROP INDEX public.idx_imagenes_principal;
postgres
idx_imagenes_producto
CREATE INDEX idx_imagenes_producto ON public.imagenes_producto USING btree (producto_id);
DROP INDEX public.idx_imagenes_producto;
postgres
idx_items_carrito_carrito
CREATE INDEX idx_items_carrito_carrito ON public.items_carrito USING btree (carrito_id);
DROP INDEX public.idx_items_carrito_carrito;
postgres
idx_items_pedido_pedido
CREATE INDEX idx_items_pedido_pedido ON public.items_pedido USING btree (pedido_id);
DROP INDEX public.idx_items_pedido_pedido;
postgres
idx_items_pedido_producto
CREATE INDEX idx_items_pedido_producto ON public.items_pedido USING btree (producto_id);
DROP INDEX public.idx_items_pedido_producto;
postgres
idx_logs_fecha
CREATE INDEX idx_logs_fecha ON public.logs_auditoria USING btree (creado_en DESC);
DROP INDEX public.idx_logs_fecha;
postgres
idx_logs_modulo
CREATE INDEX idx_logs_modulo ON public.logs_auditoria USING btree (modulo);
DROP INDEX public.idx_logs_modulo;
postgres
idx_logs_usuario
CREATE INDEX idx_logs_usuario ON public.logs_auditoria USING btree (usuario_id);
DROP INDEX public.idx_logs_usuario;
postgres
idx_mov_inventario_fecha
CREATE INDEX idx_mov_inventario_fecha ON public.movimientos_inventario USING btree (creado_en DESC);
DROP INDEX public.idx_mov_inventario_fecha;
postgres
idx_mov_inventario_prod
CREATE INDEX idx_mov_inventario_prod ON public.movimientos_inventario USING btree (producto_id);
DROP INDEX public.idx_mov_inventario_prod;
postgres
idx_mov_inventario_tipo
CREATE INDEX idx_mov_inventario_tipo ON public.movimientos_inventario USING btree (tipo);
DROP INDEX public.idx_mov_inventario_tipo;
postgres
idx_pedidos_creado
CREATE INDEX idx_pedidos_creado ON public.pedidos USING btree (creado_en DESC);
DROP INDEX public.idx_pedidos_creado;
postgres
idx_pedidos_numero
CREATE INDEX idx_pedidos_numero ON public.pedidos USING btree (numero_pedido);
DROP INDEX public.idx_pedidos_numero;
postgres
idx_pedidos_usuario
CREATE INDEX idx_pedidos_usuario ON public.pedidos USING btree (usuario_id);
DROP INDEX public.idx_pedidos_usuario;
postgres
idx_productos_activo
CREATE INDEX idx_productos_activo ON public.productos USING btree (activo) WHERE (eliminado_en IS NULL);
DROP INDEX public.idx_productos_activo;
postgres
idx_productos_categoria
CREATE INDEX idx_productos_categoria ON public.productos USING btree (categoria_id);
DROP INDEX public.idx_productos_categoria;
postgres
idx_productos_destacado
CREATE INDEX idx_productos_destacado ON public.productos USING btree (destacado) WHERE (activo = true);
DROP INDEX public.idx_productos_destacado;
postgres
idx_productos_oferta
CREATE INDEX idx_productos_oferta ON public.productos USING btree (oferta_activa) WHERE (activo = true);
DROP INDEX public.idx_productos_oferta;
postgres
idx_productos_sku
CREATE INDEX idx_productos_sku ON public.productos USING btree (sku);
DROP INDEX public.idx_productos_sku;
postgres
idx_productos_slug
CREATE INDEX idx_productos_slug ON public.productos USING btree (slug);
DROP INDEX public.idx_productos_slug;
postgres
idx_productos_stock_bajo
CREATE INDEX idx_productos_stock_bajo ON public.productos USING btree (stock) WHERE ((stock <= stock_minimo) AND (eliminado_en IS NULL));
DROP INDEX public.idx_productos_stock_bajo;
postgres
idx_usuarios_activo
CREATE INDEX idx_usuarios_activo ON public.usuarios USING btree (activo) WHERE (eliminado_en IS NULL);
DROP INDEX public.idx_usuarios_activo;
postgres
idx_usuarios_email
CREATE INDEX idx_usuarios_email ON public.usuarios USING btree (email);
DROP INDEX public.idx_usuarios_email;
postgres
idx_variantes_producto
CREATE INDEX idx_variantes_producto ON public.variantes_producto USING btree (producto_id);
DROP INDEX public.idx_variantes_producto;
postgres
idx_variantes_sku
CREATE INDEX idx_variantes_sku ON public.variantes_producto USING btree (sku);
DROP INDEX public.idx_variantes_sku;
postgres
jobs_queue_index
CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);
DROP INDEX public.jobs_queue_index;
postgres
notificaciones_stock_email_index
CREATE INDEX notificaciones_stock_email_index ON public.notificaciones_stock USING btree (email);
DROP INDEX public.notificaciones_stock_email_index;
postgres
notificaciones_stock_producto_id_notificado_index
CREATE INDEX notificaciones_stock_producto_id_notificado_index ON public.notificaciones_stock USING btree (producto_id, notificado);
DROP INDEX public.notificaciones_stock_producto_id_notificado_index;
postgres
sessions_last_activity_index
CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);
DROP INDEX public.sessions_last_activity_index;
postgres
sessions_user_id_index
CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);
DROP INDEX public.sessions_user_id_index;
postgres
usuario_permisos_usuario_id_model_type_index
CREATE INDEX usuario_permisos_usuario_id_model_type_index ON public.usuario_permisos USING btree (usuario_id, model_type);
DROP INDEX public.usuario_permisos_usuario_id_model_type_index;
postgres
pedidos trg_estado_inicial_pedido
CREATE TRIGGER trg_estado_inicial_pedido AFTER INSERT ON public.pedidos FOR EACH ROW EXECUTE FUNCTION public.registrar_estado_inicial_pedido();
DROP TRIGGER trg_estado_inicial_pedido ON public.pedidos;
postgres
facturas trg_numero_factura
CREATE TRIGGER trg_numero_factura BEFORE INSERT ON public.facturas FOR EACH ROW WHEN (((new.numero IS NULL) OR ((new.numero)::text = ''::text))) EXECUTE FUNCTION public.generar_numero_factura();
DROP TRIGGER trg_numero_factura ON public.facturas;
postgres
pedidos trg_numero_pedido
CREATE TRIGGER trg_numero_pedido BEFORE INSERT ON public.pedidos FOR EACH ROW WHEN (((new.numero_pedido IS NULL) OR ((new.numero_pedido)::text = ''::text))) EXECUTE FUNCTION public.generar_numero_pedido();
DROP TRIGGER trg_numero_pedido ON public.pedidos;
postgres
carritos trg_upd_carritos
CREATE TRIGGER trg_upd_carritos BEFORE UPDATE ON public.carritos FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_carritos ON public.carritos;
postgres
categorias trg_upd_categorias
CREATE TRIGGER trg_upd_categorias BEFORE UPDATE ON public.categorias FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_categorias ON public.categorias;
postgres
cupones trg_upd_cupones
CREATE TRIGGER trg_upd_cupones BEFORE UPDATE ON public.cupones FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_cupones ON public.cupones;
postgres
devoluciones trg_upd_devoluciones
CREATE TRIGGER trg_upd_devoluciones BEFORE UPDATE ON public.devoluciones FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_devoluciones ON public.devoluciones;
postgres
direcciones trg_upd_direcciones
CREATE TRIGGER trg_upd_direcciones BEFORE UPDATE ON public.direcciones FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_direcciones ON public.direcciones;
postgres
envios_pedido trg_upd_envios_pedido
CREATE TRIGGER trg_upd_envios_pedido BEFORE UPDATE ON public.envios_pedido FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_envios_pedido ON public.envios_pedido;
postgres
facturas trg_upd_facturas
CREATE TRIGGER trg_upd_facturas BEFORE UPDATE ON public.facturas FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_facturas ON public.facturas;
postgres
items_carrito trg_upd_items_carrito
CREATE TRIGGER trg_upd_items_carrito BEFORE UPDATE ON public.items_carrito FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_items_carrito ON public.items_carrito;
postgres
pedidos trg_upd_pedidos
CREATE TRIGGER trg_upd_pedidos BEFORE UPDATE ON public.pedidos FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_pedidos ON public.pedidos;
postgres
productos trg_upd_productos
CREATE TRIGGER trg_upd_productos BEFORE UPDATE ON public.productos FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_productos ON public.productos;
postgres
roles trg_upd_roles
CREATE TRIGGER trg_upd_roles BEFORE UPDATE ON public.roles FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_roles ON public.roles;
postgres
usuarios trg_upd_usuarios
CREATE TRIGGER trg_upd_usuarios BEFORE UPDATE ON public.usuarios FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_usuarios ON public.usuarios;
postgres
variantes_producto trg_upd_variantes_producto
CREATE TRIGGER trg_upd_variantes_producto BEFORE UPDATE ON public.variantes_producto FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_variantes_producto ON public.variantes_producto;
postgres
zonas_envio trg_upd_zonas_envio
CREATE TRIGGER trg_upd_zonas_envio BEFORE UPDATE ON public.zonas_envio FOR EACH ROW EXECUTE FUNCTION public.actualizar_timestamp();
DROP TRIGGER trg_upd_zonas_envio ON public.zonas_envio;
postgres
carritos carritos_cupon_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.carritos
    ADD CONSTRAINT carritos_cupon_id_fkey FOREIGN KEY (cupon_id) REFERENCES public.cupones(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.carritos DROP CONSTRAINT carritos_cupon_id_fkey;
postgres
carritos carritos_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.carritos
    ADD CONSTRAINT carritos_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.carritos DROP CONSTRAINT carritos_usuario_id_fkey;
postgres
categorias categorias_padre_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.categorias
    ADD CONSTRAINT categorias_padre_id_fkey FOREIGN KEY (padre_id) REFERENCES public.categorias(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.categorias DROP CONSTRAINT categorias_padre_id_fkey;
postgres
cupones cupones_categoria_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.cupones
    ADD CONSTRAINT cupones_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES public.categorias(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.cupones DROP CONSTRAINT cupones_categoria_id_fkey;
postgres
cupones cupones_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.cupones
    ADD CONSTRAINT cupones_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.cupones DROP CONSTRAINT cupones_producto_id_fkey;
postgres
devoluciones devoluciones_pedido_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.devoluciones
    ADD CONSTRAINT devoluciones_pedido_id_fkey FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id);
ALTER TABLE ONLY public.devoluciones DROP CONSTRAINT devoluciones_pedido_id_fkey;
postgres
devoluciones devoluciones_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.devoluciones
    ADD CONSTRAINT devoluciones_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);
ALTER TABLE ONLY public.devoluciones DROP CONSTRAINT devoluciones_usuario_id_fkey;
postgres
direcciones direcciones_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.direcciones
    ADD CONSTRAINT direcciones_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.direcciones DROP CONSTRAINT direcciones_usuario_id_fkey;
postgres
envios_pedido envios_pedido_pedido_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.envios_pedido
    ADD CONSTRAINT envios_pedido_pedido_id_fkey FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.envios_pedido DROP CONSTRAINT envios_pedido_pedido_id_fkey;
postgres
estados_pedido estados_pedido_pedido_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.estados_pedido
    ADD CONSTRAINT estados_pedido_pedido_id_fkey FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.estados_pedido DROP CONSTRAINT estados_pedido_pedido_id_fkey;
postgres
estados_pedido estados_pedido_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.estados_pedido
    ADD CONSTRAINT estados_pedido_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.estados_pedido DROP CONSTRAINT estados_pedido_usuario_id_fkey;
postgres
facturas facturas_pedido_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.facturas
    ADD CONSTRAINT facturas_pedido_id_fkey FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id);
ALTER TABLE ONLY public.facturas DROP CONSTRAINT facturas_pedido_id_fkey;
postgres
facturas facturas_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.facturas
    ADD CONSTRAINT facturas_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);
ALTER TABLE ONLY public.facturas DROP CONSTRAINT facturas_usuario_id_fkey;
postgres
imagenes_producto imagenes_producto_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.imagenes_producto
    ADD CONSTRAINT imagenes_producto_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.imagenes_producto DROP CONSTRAINT imagenes_producto_producto_id_fkey;
postgres
items_carrito items_carrito_carrito_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.items_carrito
    ADD CONSTRAINT items_carrito_carrito_id_fkey FOREIGN KEY (carrito_id) REFERENCES public.carritos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.items_carrito DROP CONSTRAINT items_carrito_carrito_id_fkey;
postgres
items_carrito items_carrito_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.items_carrito
    ADD CONSTRAINT items_carrito_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.items_carrito DROP CONSTRAINT items_carrito_producto_id_fkey;
postgres
items_carrito items_carrito_variante_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.items_carrito
    ADD CONSTRAINT items_carrito_variante_producto_id_fkey FOREIGN KEY (variante_producto_id) REFERENCES public.variantes_producto(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.items_carrito DROP CONSTRAINT items_carrito_variante_producto_id_fkey;
postgres
items_pedido items_pedido_pedido_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.items_pedido
    ADD CONSTRAINT items_pedido_pedido_id_fkey FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.items_pedido DROP CONSTRAINT items_pedido_pedido_id_fkey;
postgres
items_pedido items_pedido_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.items_pedido
    ADD CONSTRAINT items_pedido_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES public.productos(id);
ALTER TABLE ONLY public.items_pedido DROP CONSTRAINT items_pedido_producto_id_fkey;
postgres
items_pedido items_pedido_variante_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.items_pedido
    ADD CONSTRAINT items_pedido_variante_producto_id_fkey FOREIGN KEY (variante_producto_id) REFERENCES public.variantes_producto(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.items_pedido DROP CONSTRAINT items_pedido_variante_producto_id_fkey;
postgres
lista_deseos lista_deseos_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.lista_deseos
    ADD CONSTRAINT lista_deseos_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.lista_deseos DROP CONSTRAINT lista_deseos_producto_id_fkey;
postgres
lista_deseos lista_deseos_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.lista_deseos
    ADD CONSTRAINT lista_deseos_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.lista_deseos DROP CONSTRAINT lista_deseos_usuario_id_fkey;
postgres
logs_auditoria logs_auditoria_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.logs_auditoria
    ADD CONSTRAINT logs_auditoria_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.logs_auditoria DROP CONSTRAINT logs_auditoria_usuario_id_fkey;
postgres
movimientos_inventario movimientos_inventario_pedido_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_pedido_id_fkey FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.movimientos_inventario DROP CONSTRAINT movimientos_inventario_pedido_id_fkey;
postgres
movimientos_inventario movimientos_inventario_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES public.productos(id);
ALTER TABLE ONLY public.movimientos_inventario DROP CONSTRAINT movimientos_inventario_producto_id_fkey;
postgres
movimientos_inventario movimientos_inventario_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.movimientos_inventario DROP CONSTRAINT movimientos_inventario_usuario_id_fkey;
postgres
movimientos_inventario movimientos_inventario_variante_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_variante_producto_id_fkey FOREIGN KEY (variante_producto_id) REFERENCES public.variantes_producto(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.movimientos_inventario DROP CONSTRAINT movimientos_inventario_variante_producto_id_fkey;
postgres
notificaciones_stock notificaciones_stock_producto_id_foreign
FK CONSTRAINT
ALTER TABLE ONLY public.notificaciones_stock
    ADD CONSTRAINT notificaciones_stock_producto_id_foreign FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.notificaciones_stock DROP CONSTRAINT notificaciones_stock_producto_id_foreign;
postgres
opciones_variante opciones_variante_tipo_variante_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.opciones_variante
    ADD CONSTRAINT opciones_variante_tipo_variante_id_fkey FOREIGN KEY (tipo_variante_id) REFERENCES public.tipos_variante(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.opciones_variante DROP CONSTRAINT opciones_variante_tipo_variante_id_fkey;
postgres
pedidos pedidos_cupon_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_cupon_id_fkey FOREIGN KEY (cupon_id) REFERENCES public.cupones(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.pedidos DROP CONSTRAINT pedidos_cupon_id_fkey;
postgres
pedidos pedidos_direccion_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_direccion_id_fkey FOREIGN KEY (direccion_id) REFERENCES public.direcciones(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.pedidos DROP CONSTRAINT pedidos_direccion_id_fkey;
postgres
pedidos pedidos_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);
ALTER TABLE ONLY public.pedidos DROP CONSTRAINT pedidos_usuario_id_fkey;
postgres
pedidos pedidos_zona_envio_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.pedidos
    ADD CONSTRAINT pedidos_zona_envio_id_fkey FOREIGN KEY (zona_envio_id) REFERENCES public.zonas_envio(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.pedidos DROP CONSTRAINT pedidos_zona_envio_id_fkey;
postgres
producto_del_mes producto_del_mes_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.producto_del_mes
    ADD CONSTRAINT producto_del_mes_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.producto_del_mes DROP CONSTRAINT producto_del_mes_producto_id_fkey;
postgres
productos productos_brand_id_foreign
FK CONSTRAINT
ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_brand_id_foreign FOREIGN KEY (brand_id) REFERENCES public.brands(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.productos DROP CONSTRAINT productos_brand_id_foreign;
postgres
productos productos_categoria_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.productos
    ADD CONSTRAINT productos_categoria_id_fkey FOREIGN KEY (categoria_id) REFERENCES public.categorias(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.productos DROP CONSTRAINT productos_categoria_id_fkey;
postgres
promociones_envio_gratis promociones_envio_gratis_zona_envio_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.promociones_envio_gratis
    ADD CONSTRAINT promociones_envio_gratis_zona_envio_id_fkey FOREIGN KEY (zona_envio_id) REFERENCES public.zonas_envio(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.promociones_envio_gratis DROP CONSTRAINT promociones_envio_gratis_zona_envio_id_fkey;
postgres
reenvios_factura reenvios_factura_factura_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.reenvios_factura
    ADD CONSTRAINT reenvios_factura_factura_id_fkey FOREIGN KEY (factura_id) REFERENCES public.facturas(id);
ALTER TABLE ONLY public.reenvios_factura DROP CONSTRAINT reenvios_factura_factura_id_fkey;
postgres
reenvios_factura reenvios_factura_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.reenvios_factura
    ADD CONSTRAINT reenvios_factura_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE SET NULL;
ALTER TABLE ONLY public.reenvios_factura DROP CONSTRAINT reenvios_factura_usuario_id_fkey;
postgres
rol_permisos rol_permisos_permiso_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.rol_permisos
    ADD CONSTRAINT rol_permisos_permiso_id_fkey FOREIGN KEY (permiso_id) REFERENCES public.permisos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.rol_permisos DROP CONSTRAINT rol_permisos_permiso_id_fkey;
postgres
rol_permisos rol_permisos_rol_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.rol_permisos
    ADD CONSTRAINT rol_permisos_rol_id_fkey FOREIGN KEY (rol_id) REFERENCES public.roles(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.rol_permisos DROP CONSTRAINT rol_permisos_rol_id_fkey;
postgres
usos_cupon usos_cupon_cupon_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.usos_cupon
    ADD CONSTRAINT usos_cupon_cupon_id_fkey FOREIGN KEY (cupon_id) REFERENCES public.cupones(id);
ALTER TABLE ONLY public.usos_cupon DROP CONSTRAINT usos_cupon_cupon_id_fkey;
postgres
usos_cupon usos_cupon_pedido_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.usos_cupon
    ADD CONSTRAINT usos_cupon_pedido_id_fkey FOREIGN KEY (pedido_id) REFERENCES public.pedidos(id);
ALTER TABLE ONLY public.usos_cupon DROP CONSTRAINT usos_cupon_pedido_id_fkey;
postgres
usos_cupon usos_cupon_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.usos_cupon
    ADD CONSTRAINT usos_cupon_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id);
ALTER TABLE ONLY public.usos_cupon DROP CONSTRAINT usos_cupon_usuario_id_fkey;
postgres
usuario_roles usuario_roles_rol_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.usuario_roles
    ADD CONSTRAINT usuario_roles_rol_id_fkey FOREIGN KEY (rol_id) REFERENCES public.roles(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.usuario_roles DROP CONSTRAINT usuario_roles_rol_id_fkey;
postgres
usuario_roles usuario_roles_usuario_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.usuario_roles
    ADD CONSTRAINT usuario_roles_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.usuario_roles DROP CONSTRAINT usuario_roles_usuario_id_fkey;
postgres
variante_opciones variante_opciones_opcion_variante_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.variante_opciones
    ADD CONSTRAINT variante_opciones_opcion_variante_id_fkey FOREIGN KEY (opcion_variante_id) REFERENCES public.opciones_variante(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.variante_opciones DROP CONSTRAINT variante_opciones_opcion_variante_id_fkey;
postgres
variante_opciones variante_opciones_variante_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.variante_opciones
    ADD CONSTRAINT variante_opciones_variante_producto_id_fkey FOREIGN KEY (variante_producto_id) REFERENCES public.variantes_producto(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.variante_opciones DROP CONSTRAINT variante_opciones_variante_producto_id_fkey;
postgres
variantes_producto variantes_producto_producto_id_fkey
FK CONSTRAINT
ALTER TABLE ONLY public.variantes_producto
    ADD CONSTRAINT variantes_producto_producto_id_fkey FOREIGN KEY (producto_id) REFERENCES public.productos(id) ON DELETE CASCADE;
ALTER TABLE ONLY public.variantes_producto DROP CONSTRAINT variantes_producto_producto_id_fkey;
postgres
0e[|J3Oe
1/`(hez_p
#S24N?c4
:OAyEk`C
AYW^1Q0Q&
