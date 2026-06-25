Bitácora · Mini-entrega N — [Facade Adapter]
Nombre: Adrian Samuel Lopez Pimentel Fecha: 2026-06-24 Mini-entrega: N 4 Módulo refactorizado: [Payment / Payment Controller / Customer]

1. ¿Qué problema de diseño identifiqué en el legacy?
El problema de diseño que identifique, fue que en el payment controller se realizaba la validación, decidia que proveedor usar, instanciaba los handlers, ejecutaba pagos, escribia logs, etc... eso lo convertia en una clase que violaba el principio de Single Responsibility, ya que se encargaba de demasiadas cosas. Tambien violaba el principio OCP, ya que para agregar algo teniamos que buscar y modificar en el if que esta clase poseia.

Otro problema de diseño que se encontro estaba ubicado en customer service, el cual se encargaba de validar el cliente, validar el vendedor, calcular el subtotal, aplicar descuento, calcular delivery, crear la orden, reserva stock, procesar pago y mas tareas. Esto es demasiado para un controller, viola SingleResponsibility y OCP, ya que el controller tenia mas de 200 lineas. Ademas dentro de ese controller habia mucha logica compleja. Del mismo modo todo eso hace que agregar nuevas funciones sea muy complicado.

2. ¿Qué patrón aplicaste y por qué resuelve este problema?
Para resolver el problema de payment controller, apliqué el patrón adapter, ya que cada proveedor tenia una forma diferente de trabajar, por ejemplo, wompi usaba campos como estado e id de transaccion pero n1co usaba makepayment y devolvia estatus y payment_id. Para resolver eso cree una interfaz para todos los proveedores para que asi payment controller solo tenga que llamar a un metodo change(). De este modo el controller no tiene que conocer nada sobre las pasarelas de pago, toda esa logica que antes estaba en payment controller ahora esta distribuida en una interfaz comun y las interfaces adaptadoras. Esto resuelve el problema, porque ahora payment controller ya no tiene multiples if, y deja de crear una respuesta especifica para cada proveedor, lo que hace el codigo mas limpio y facil de extender.

Con respecto a customer apliqué el patron facade ya que este nos permite ocultar todo el proceso complejo que estaba en el model usando una interfaz. Usando el patrón facade, logramos simplificar la logica extensa que estaba en el modelo customer.

3. ¿Qué patrón descartaste y por qué?
El patrón que descarté fue el patrón Strategy, es decir usar diferentes estrategias para cada funcion de pago, pero descarte esto porque ya tenia los handlers de cada metodo de pago, entonces solo necesitaba crear clases que me ayudaran a traducir la información de cada una, también descarte strategy debido a que el patron strategy se usa para cosas mas simples, es decir cuando tenemos varias funciones que hace lo mismo pero diferente, y en este caso cada clase handler, lo hace de manera totalmente diferente. Entonces por esa razon descarte el patron strategy, asi mismo en el moelo customer no use este patron ya que eran demasiadas funciones las que hacia customer y usar strategy ademas de que no realizaban las mismas tareas, tambien me iba a llenar de archivos extra y complejidad extra.

4. ¿Qué trade-off aceptaste?
El trade-off que acepte en que al haber creado varios adaptadores, dto y un contract, ahora el sistema usa mas archivos para funcionar, lo que a largo plazo puede ser un poco confuso, pero los beneficios lo valen. Con respecto a customer el trade off que acepte fue separar la lógica del modelo y guardarla en un archivo el cual contiene todas las funciones que realiza, esto hace que la logica de customer este algo oculta.

5. ¿Qué cambiarías si tuvieras que hacerlo de nuevo?
Si tuviera que hacerlo de nuevo lo que cambiaria seria que para customer en la logica de cada funcion, yo separaria las funciones en archivos diferentes para no tener todas las funciones en un solo archivo.
