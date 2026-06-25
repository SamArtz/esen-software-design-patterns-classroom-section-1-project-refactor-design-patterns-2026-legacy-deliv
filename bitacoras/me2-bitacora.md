@@ -1,46 +1,58 @@
# Bitácora · Mini-entrega N — [Título del patrón]

**Módulo refactorizado:** [ej. Descuentos / Notificaciones / Estados de Order]
**Nombre:** Adrian Samuel Lopez Pimentel
**Fecha:** 2026-05-29
**Mini-entrega:** N 2
**Módulo refactorizado:** [Descuentos / Notificaciones / Estados de Order]

---

## 1. ¿Qué problema de diseño identifiqué en el legacy?


El sistema de descuentos presentaba un problema de Acoplamiento Rígido y violación del Principio de Abierto/Cerrado (OCP). El modelo Discount actuaba como un "Modelo Dios" que asumía la responsabilidad total de conocer y ejecutar la lógica matemática de cada tipo de beneficio, porcentaje, BOGO, envío gratis, etc.

Esto causaba un problema real de escalabilidad y fragilidad para añadir una nueva categoría de descuento como "Suscripciones" o "Transporte", se debía modificar todo el modelo. También, este tenia un switch enorme que se encargaba de toda la lógica lo que lo hacia muy dificil de mantener y entender.

Luego nos pasamos a Order.php el cual identifique como un Objeto Todopoderoso. Esto viola el prinicipio de responsabilidad única y el principio abierto cerrado. 

Tambien en este mismo Modelo existía el metodo validateOrder el cual se encargaba de validar todo, desde que existiera un cliente hasta que hubiera stock físico. Esto lo tenia usando un switch y varios if lo que hacia que el sistema fuera muy rigido y que al intentar agregar una validación para alguna categoria habrian mas probabilidades de causar un error.

---

## 2. ¿Qué patrón aplicaste y por qué resuelve este problema?


Apliqué el patrón Strategy para desacoplar el cálculo de los beneficios del modelo Discount. En lugar de un switch que decide cómo calcular basándose en un string, el modelo ahora delega el trabajo a un objeto especializado.

Clases modificadas y nuevas


Discount, apply()
DiscountStrategy, PercentageStrategy, BogoStrategy, FixedAmountStrategy, calculate(Order $order, Discount $discount)

---
En este caso de discount, en vez de llevar la logica en un if para decidir que descuento aplicar, se crearon clases separadas las cuales heredan DiscountStrategy la que contiene el metodo calculate, y asi aplicamos polimorfismo para llamar siempre al mismo metodo.

Para el modelo Order apliqué el patrón Observer mediante el sistema de Eventos de Laravel. Ahora el modelo order ya no crea objetos  de servicios o correos o inventario, sino que mediante el sistema de eventos ahora el archivo se encarga de avisar el estado que posee

Clases modificadas y creadas
Modificada: Order, trasitionto()
Creadas: OrderStatusChanged, esta contiene que pedido es y su nuevo estado
Creada: HandleOrderStatusActions, esta se encarga de recibir el mensaje si ha cambiado el estado del pedido

## 3. ¿Qué patrón descartaste y por qué?


Para Order descarte el patron Chain of Responsibility, la principal razón por la que descarte este patron es porque tiene un acoplamiento secuencial, es decir que depende de varias acciones ocurriendo en el mismo orden. Y al usar el Observer todas las acciones son independientes

---

## 4. ¿Qué trade-off aceptaste?


El trade-off puede ser que cambiamos el codigo el cual estaba en un solo archivo por distintos archivos con codigo separado como Legos. Pase de tener 2 archivos por lógica, a tener 6 archivos, ahora para ver la logica de discount hay que ir a la carpeta services y revisar discount y escoger el descuento a modificar de los 6.

Otro trade-off es el rastreo de errores, ahora al tener varios archivos la logica se separa, hay que ser mas cuidadosos con los imports, tambien cuando mandamos a llamar a una funcion debemos procurar enviarle el objeto correcto o tipo de dato.

El sacrificio valio la pena por que es preferible tener mas archivos a tener una lógica dificil de leer y escalar.

---

## 5. ¿Qué cambiarías si tuvieras que hacerlo de nuevo?


Lo que hubiera hecho es separar la logica de validacion de los productos del modelo Order, para asi separar esa responsabilidad del modelo y dejarlo en otro archivo como en servicios. Y asi hacer que el modelo sea aun mas independiente