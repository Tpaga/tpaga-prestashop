[![Code Climate](https://codeclimate.com/github/Tpaga/tpaga-prestashop/badges/gpa.svg)](https://codeclimate.com/github/Tpaga/tpaga-prestashop)

# Tpaga Web Checkout para Prestashop

Tpaga WebCheckout es un servicio de Tpaga con el cual sus clientes podrán realizar pagos de manera agíl y sencilla. Este módulo es una implimentación de Tpaga WebCheckout para Prestashop (Nos encontramos en Beta).

## Requerimientos mínimos

  - Prestashop >= 1.5 (Probado en 1.6.1.6)

## Instalación del módulo en prestashop (Developer)

1. Descargue el Zip de este repositorio. https://github.com/Tpaga/tpaga-prestashop/releases/download/v1.1-beta/tpaga.zip
2. Ingrese a la ruta de Modulos y servicios en su instalación de Prestashop (1.6)
3. De clic en la opción de Agregar Nuevo Modulo, en la parte inferior aparecerá una nueva opción de título Agregar Nuevo Módulo.
4. Seleccione el archivo descargado en el punto 1, y de clic en subir archivo.
5. Una vez cargado el modulo, por favor vaya a al listado de módulos y busque el modulo de Tpaga
6. De clic en instalar, se desplegará un mensaje de advertencia, de clic en "Continuar con la Instalación".
7. Una vez instalado el módulo, en el listado de módulos busque: Tpaga y de clic en configurar
8. Ya en el panel, diligencie los campos `Merchant Token` y `Secret` estos son generados al registrase en Tpaga WebCheckout.
9. Si desea hacer transacciones de prueba seleccione la opción `True` en `Modo de prueba`.
