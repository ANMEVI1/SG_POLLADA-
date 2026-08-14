# 🔄 Flujo de Trabajo y Casos Reales (¿Qué pasaría si...?)

Esta guía está diseñada para explicar cómo funciona el sistema en la práctica, paso a paso, y resolver esas dudas comunes sobre imprevistos que ocurren en pleno ajetreo del evento. 

---

## 📅 El Flujo de Trabajo Ideal (Paso a Paso)

Imagina que es el día del evento. Así es como deberías usar el sistema desde tu teléfono:

1. **Apertura de Caja (¡El primer paso obligatorio!)**
   - Llegas al evento, abres el celular y vas a **Cuadre de Caja**. 
   - Presionas "🟢 Abrir Jornada". Te pedirá tu PIN de 4 dígitos (1013 por defecto). 
   - *¿Por qué es necesario?* Porque a partir de este segundo, el sistema empezará a registrar que todo el dinero que entre o salga pertenece a "este día" o "este turno".

2. **Preparativos y Gastos de Última Hora**
   - Entras a la sección **Inversión**. Si durante la mañana compraste verduras o hielo de tu bolsillo, lo anotas. 
   - Si más tarde abres la caja para sacar S/20 para comprar servilletas, lo anotas en **Egresos** y *muy importante*, marcas la casilla **"Salió de caja"**.

3. **¡Empieza la Acción! (Entregas y Cobros)**
   - Vas a la sección **Entregas**. La gente empieza a hacer cola.
   - Llega un cliente y te da su código (ej. "Tengo el código 2045"). Lo buscas rápido en la barra superior.
   - Le das su pedido y tocas **"Entregar"**. 
   - Te da el dinero en la mano. Tocas el botón que dice "Pagar" 1 vez y cambiará a **"💵 Efectivo"**. 
   - ¡Listo! Atiendes al siguiente.

4. **Fin de la Jornada (El Cuadre)**
   - Se acabó la pollada. Estás cansado pero necesitas saber cómo te fue.
   - Vas a **Cuadre de Caja** y presionas "🔴 Cerrar Jornada".
   - El sistema te dirá: *"Ingresa tu PIN y dime cuánto efectivo físico (billetes y monedas) tienes en tu caja ahora mismo"*. 
   - Cuentas tu dinero, lo escribes (ej. 350.50) y aceptas. 
   - El sistema comparará mágicamente lo que contaste con lo que *debería* haber según lo que marcaste en todo el día, y te dirá si falta plata, sobra plata, o cuadra perfecto.

---

## 🤔 Casos Comunes en el Ajetreo: ¿Qué pasaría si...?

### ❌ "Le entregué a Juan por error en la app, pero no se lo he dado en la vida real"
**Solución:** Vuelves a presionar el botón "Entregado ✓" de Juan. 
**¿Qué pasará?** Como deshacer una entrega es delicado (borra la venta de la base de datos), el sistema te lanzará una alerta roja y te pedirá tu **PIN**. Lo ingresas y la entrega volverá a estar pendiente, como si nada hubiera pasado.

### 💸 "Marqué que María me pagó, pero luego me di cuenta que se fue sin pagar (le fié)"
**Solución:** Tocas el botón de pago de María hasta que regrese al estado gris "Pagar" (Pendiente). 
**¿Qué pasará?** Como le estás "quitando" dinero a la caja al anular ese pago, el sistema no te dejará hacerlo libremente. Te pedirá el **PIN**. Ingresas el PIN y el sistema descontará ese dinero de tu total automáticamente, dejándola como deudora.

### 📱 "Marqué Efectivo, pero en realidad me enseñó la captura de Yape"
**Solución:** Simplemente toca de nuevo el botón de "💵 Efectivo".
**¿Qué pasará?** Cambiará a "📱 Yape". Esto no te pide PIN porque el dinero sigue ingresando al sistema, solo que le estás avisando a la caja que busque ese dinero en el celular y no en físico.

### 🏃‍♂️ "Me quedé sin mayonesa y mandé a alguien a comprar con billetes de la caja"
**Solución:** Vas a Inversión > Registrar Egreso. Anotas "Mayonesa - S/15". 
**¡Súper importante!** Asegúrate de marcar la casilla **"Salió de caja"**.
**¿Qué pasará?** El sistema entenderá que sacaste esos 15 soles del balde de dinero. A la hora de hacer tu cuadre final, el sistema restará esos 15 soles del "Efectivo Teórico", por lo que al final del día no te asustarás pensando que se perdió plata.

### 🕵️ "Son las 4 PM, quiero ver quiénes me deben plata (los fiados)"
**Solución:** Vas a la sección Entregas y presionas el filtro/botón arriba que dice **"Deudores"**.
**¿Qué pasará?** El sistema ocultará a todos los clientes y te mostrará *únicamente* a las personas a las que ya les diste su pollada pero su botón sigue en "Pagar" (gris). Así puedes ir a cobrarles de inmediato.

### ⚖️ "¿Qué significa 'Descuadre' cuando cierro mi caja?"
- Si el descuadre dice **S/ 0.00**: ¡Eres un genio! Todo el dinero físico que contaste coincide a la perfección con los botones de "Efectivo" que tocaste en la app durante el día.
- Si el descuadre dice **S/ -10.00 (Rojo)**: Significa que te faltan 10 soles físicos. Probablemente le diste vuelto de más a alguien, alguien te pagó incompleto y no te diste cuenta, o te olvidaste de registrar que sacaste plata de la caja para comprar algo.
- Si el descuadre dice **S/ +5.00 (Verde)**: Significa que tienes 5 soles de más en tu balde. Probablemente alguien no quiso vuelto, o marcaste a alguien como "Yape" cuando en realidad te pagó en monedas.
