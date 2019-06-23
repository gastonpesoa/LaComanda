# LaComanda
Requerimientos de la aplicación.

Debemos realizar un sistema según las necesidades y deseos del cliente, para eso tenemos una breve descripción de lo que el cliente nos comenta acerca de su negocio. 

“Mi restaurante tiene un servicio de la más alta calidad, con cuatro sectores bien diferenciados: la barra de tragos y vinos, en nuestra entrada; en el patio trasero se encuentra la barra de choperas de cerveza artesanal; la cocina, donde se preparan todos los platos de comida; y nuestroCandy Bar​, que se encarga de la preparación de postres artesanales. 

Dentro de nuestro plantel de trabajadores tenemos muchos empleados que son trabajadores golondrinas, por lo cual, tenemos mucha rotación de personal, pero los tenemos bien diferenciados entre los ​#bartender​ , los ​#cerveceros​, los ​#cocineros​, los ​#mozos​  y los que podemos controlar todo incluso los pagos, que somos cualquiera de los tres ​#socios​ del local.

Necesitamos que cada comanda tenga la información necesaria, incluso el nombre del cliente, y que sea vista por el empleado correspondiente. La operatoria principal sería:

* Si al mozo le hacen un pedido de un vino, una cerveza y unas empanadas, deberían los empleados correspondientes ver estos pedidos en su listado de “pendientes”, con la opción de tomar una foto de la mesa con sus integrantes y relacionarlo con el pedido.

* El mozo le da un código único alfanumérico (de 5 caracteres) al cliente que le permite identificar su pedido.

* El empleado que toma ese pedido para prepararlo, al momento de hacerlo, debe cambiar el estado de ese pedido a “en preparación” y agregarle un tiempo estimado de finalización, teniendo en cuenta que puede haber más de un empleado en el mismo puesto. Ej: dos bartender o tres cocineros.

* El empleado que toma ese pedido para prepararlo debe poner el estado “listo para servir”, cuando el pedido esté listo.

* Cualquiera de los socios pude ver, en todo momento, el estado de todos los pedidos.

* Las mesas tienen un código de identificación único (de 5 caracteres) , el cliente al entrar en nuestra aplicación puede ingresar ese código junto con el del pedido y se le mostrará el tiempo restante para su pedido.

* La mesa se  puede estar con los siguientes estados: “con cliente esperando pedido” ,”con cliente comiendo”, “con cliente pagando” y “cerrada”. La acción de cambiar el estado a “cerrada” la realiza únicamente uno de los socios.  Los estados anteriores son cambiados por el mozo.

* Al terminar de comer se habilita una encuesta con una puntuación del 1 al 10 para:

● La mesa.
● El restaurante.
● El mozo.
● El cocinero.

Y un breve texto de hasta 66 caracteres describiendo la experiencia (buena o mala) que tuvo en su atención.

Yo, como administrador del sistema, necesito ver:

(Necesito ver o de una fecha en particular o  en un lapso de tiempo.)

- De los empleados:
  a-Los días y horarios que se Ingresaron al sistema.
  b-Cantidad de operaciones de todos por sector.
  c-Cantidad de operaciones de todos por sector, listada por cada empleado.
  d-Cantidad de operaciones de cada uno por separado.
  e-Posibilidad de dar de alta a nuevos, suspenderlos o borrarlos.

- De las pedidos:
  a-Lo que más se vendió.
  b-Lo que menos se vendió.
  c-Los que no se entregaron en el tiempo estipulado.
  d-Los cancelados.
  
- De las mesas:
  a-La más usada.
  b-La menos usada.
  c-La que más facturó.
  d-La que menos facturó.
  e-La/s que tuvo la factura con el mayor importe.
  f-La/s que tuvo la factura con el menor importe.
  g-Lo que facturó entre dos fechas dadas.
  h-Mejores comentarios.
  i-Peores comentarios.
