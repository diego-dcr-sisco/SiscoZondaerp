<div id='calendar'></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Hoy'
            },
            events: @json($events),
            editable: true, // Habilita la edición de eventos (arrastrar y soltar)
            droppable: true, // Permite soltar elementos en el calendario
            eventDrop: function(info) {
                console.log(info.event.start)
                var formData = new FormData();
                formData.append("orderId", info.event.id);
                formData.append("date", info.event.start);

                $.ajax({
                    type: "POST",
                    url: '{{ route('dashboard.planning.update') }}',
                    contentType: false,
                    processData: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                    data: formData,
                    success: function(response) {
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        //console.error("Error al enviar la solicitud AJAX:", error);
                    }
                });
            }
        });
        calendar.setOption('locale', 'es');
        calendar.render();
    });
</script>
