<template>
    <div>
        <vue-cal :disable-views="['years', 'year', 'month', 'week']"
                 :selected-date="this.startDate"
                 active-view="day"
                 :split-days="this.label_rooms"
                 :time-from="8 * 60"
                 :time-to="20 * 60"
                 :time-step="5"
                 :events="events"
                 :min-split-width="250"
                 sticky-split-labels>
            <template v-slot:split-label="{ split, view }">
                <div @click="showModal(split)">
                    <i class="far fa-fw fa-building"></i> <span>&nbsp;</span>
                    <strong :style="`color: ${split.color}`">{{ split.label }}</strong>
                </div>
            </template>
            <template v-slot:event="{ event, view }">
                <div>
                    <div class="vuecal__event-time">
                        <!-- Using Vue Cal injected Date prototypes -->
                        <span>{{ event.start.formatTime("HH:mm") }}</span> -
                        <span>{{ event.end.formatTime("HH:mm") }}</span>
                    </div>


                    <div class="vuecal__event-title" v-html="event.title"/>

                    <div class="vuecal__event-content"><i :class="event.icon"></i></div>

                    <div class="vuecal__event-content text-capitalize">{{event.authors}}</div>

<!--                    <div class="vuecal__event-content text-capitalize">{{event.type}} <span
                        v-if="sessionText(event.type)"> Session</span></div>-->
                    <div class="vuecal__body mt-3" @click="openDetails(event)">
                        <button class="btn btn-outline-light">More Info</button>
                    </div>

                </div>
                <modal v-show="eventModalVisible" :data="modalEvent" @close="closeEventModal"/>
            </template>
        </vue-cal>
        <modal v-show="isModalVisible" :data="modalData" @close="closeModal"/>

    </div>
</template>

<script>
    import VueCal from 'vue-cal'
    import Modal from './Modal';
    import 'vue-cal/dist/vuecal.css'
    import {format} from 'date-fns';

    export default {
        name: "CalendarTable.vue",
        components: {VueCal, 'modal': Modal},
        props: ['items', 'rooms', 'posts'],

        data: () => {
            return {
                rendered_rooms: [],
                label_rooms: [],
                rendered_events: [],
                colors: ['blue', 'green', 'orange', 'yellow', 'red'],
                isModalVisible: false,
                eventModalVisible: false,
                startDate: '',
                modalData: {
                    title: '',
                    body: ''
                },
                modalEvent: {
                    title: '',
                    body: ''
                },
                events: [],

                modalHTML: {
                    title: "Event Guide",
                    body: `<div>
                        <p>In questa sezione è possibile gestire gli eventi.</p>
                        <p>Cliccando sul pulsante  <i class="fa fa-info-circle fa-fw"></i> è possibile aggiungere un nuovo evento.</p>
                        <p>Nella tabella presente al centro della pagina è possibile, per ogni item, effetuare delle operazioni.</p>
                        <p>Il pulsante <i aria-hidden="true" class="fas fa-eye fa-1x fa-lg"></i> permette di vedere i dettagli di questo item.</p>
                        <p>Il pulsante <i aria-hidden="true" class="fas fa-pencil-alt fa-1x fa-lg"></i> permette di modificare questo item.</p>
                        <p>Il pulsante <i aria-hidden="true" class="fas fa-minus-circle fa-1x fa-lg"></i> permette di cancellare questo item.</p>
                        <p>Gli eventi non potranno avere sovrapposizioni di orario e di classe.</p>
                        </div>
                    `
                }

            }
        },

        mounted() {
            console.log(this.rooms);
            this.rendered_rooms = JSON.parse(this.rooms);
            this.rendered_items = JSON.parse(this.items);
            this.rendered_posts = JSON.parse(this.posts);
            console.log(this.rendered_rooms);

            this.startDate = this.firstDate(this.rendered_items);
            console.log(this.startDate);


            console.log(this.rendered_items);

            this.rendered_rooms.forEach((room, index) => {
                let obj = {
                    label: room.name,
                    address: room.address,
                    city: room.city,
                    url: room.url,
                    description: room.description,
                    class: 'split-' + parseInt(index + 1),
                    color: this.colors[index],
                    id: room.id
                }
                this.label_rooms.push(obj);
            });

            console.log(this.label_rooms);

            this.rendered_items.forEach((evt, index) => {
                console.log(evt);

                let room_index = this.label_rooms.findIndex(x => x.id === evt.room);

                console.log(room_index);

                let icon = ''

                if (evt.type === 'poster') {
                    icon = 'fa-file'
                } else if (evt.type === 'break') {
                    icon = 'fa-stopwatch'
                } else if (evt.type === 'special') {
                    icon = 'fa-bahai'
                } else if (evt.type === 'plenary') {
                    icon = 'fa-clipboard-list'
                } else if (evt.type === 'parallel') {
                    icon = 'fa-volume-up'
                } else {
                    icon = 'fa-microphone'
                }

                let obj = {
                    start: format(new Date(evt.start), 'yyyy-MM-dd HH:mm'),
                    end: format(new Date(evt.end), 'yyyy-MM-dd HH:mm'),
                    title: evt.title,
                    type: evt.type,
                    authors: evt.authors,
                    room: evt.room_fk,
                    description: evt.description,
                    icon: 'fas fa-1x fa-lg ' + icon,
                    content: '<i class="fas fa-pencil-alt fa-1x fa-lg"></i>',
                    class: evt.type + '-event',
                    //split: room_index + 1,
                    split: evt.room,
                    deletable: false,
                    resizable: false,
                    draggable: false
                }

                console.log(obj);
                this.events.push(obj);
            });

            console.log(this.events);


        },

        methods: {
            sessionText(text) {
                return text === 'plenary' || text === 'poster' || text === 'parallel';
            },

            showModal(data) {
                this.modalData.title = data.label;
                this.modalData.body = `<div class="text-center">
                <h2>${data.label}</h2><hr>
                ${data.address ? '<p><i class="fa fa-map-pin"></i> ' + data.address + '</p>' : ''}
                ${data.city ? '<p><i class="fa fa-city"></i> ' + data.city + '</p>' : ''}
                ${data.url ? '<p><i class="fa fa-play"></i> ' + '<a href="'+data.url+'" target="_blank"> Link Meet</a></p>' : ''}
                ${data.description ? '<hr>'+ data.description : ''}

                </div>`;
                this.isModalVisible = true;
            },
            closeModal() {
                this.isModalVisible = false;
            },


            openDetails(event) {
                console.log(this.rendered_posts);
                let post = this.rendered_posts.find(item => item.title === event.title);
                console.log(post);
                this.modalEvent.title = event.title;
                this.modalEvent.body = `<h2>${event.title}</h2><hr>
                <p><i class="fa fa-clock"></i> ${event.start.format("DD/MM/YYYY HH:mm")} - ${event.end.format("DD/MM/YYYY HH:mm")}</p>
                <p><i class="fa fa-door-closed"></i> ${event.room.name}</p>
                <p class="text-capitalize"><i class="fa fa-list"></i> ${event.type} ${this.sessionText(event.type) ? 'session' : ''}</p>`;


                if (post) {
                    let authors = ''
                    post.authors.forEach((author, idx) => {
                        authors += `<span>${author.firstname} ${author.lastname}</span>`;
                        if (idx != post.authors.length - 1) {
                            authors += ' - '
                        }
                    })
                    this.modalEvent.body += `<p><i class="fa fa-users"></i> ${post.user_fk.name} ${post.user_fk.surname} - ${authors}</p>`;
                    if (post.pdf) {
                        this.modalEvent.body += `<p><i class="fa fa-file-pdf"></i> <a href="${post.pdf}" target="_blank">Download Pdf</a> </p>`
                    }
                }

                this.modalEvent.body += `${event.description ? '<hr>' + event.description : ''}`;

                this.eventModalVisible = true;
            },

            closeEventModal() {
                this.eventModalVisible = false
            },

            firstDate(events) {

                events.filter(evt => new Date(evt.start) > new Date());

                events.sort(function (a, b) {
                    // Turn your strings into dates, and then subtract them
                    // to get a value that is either negative, positive, or zero.
                    return new Date(a.start) - new Date(b.start);
                });


                return format(new Date(events[0].start), 'yyyy-MM-dd');
            }
        },
        computed: {}
    }
</script>

<style lang="scss">
    .vuecal .day-split-header {
        font-size: 18px;
    }

    .vuecal__body .split-1 {
        background-color: rgba(226, 242, 253, 0.7);
    }

    .vuecal__body .split-2 {
        background-color: rgba(232, 245, 233, 0.7);
    }

    .vuecal__body .split-3 {
        background-color: rgba(255, 243, 224, 0.7);
    }

    .vuecal__body .split-4 {
        background-color: rgba(255, 235, 238, 0.7);
    }

    .vuecal__no-event {
        display: none;
    }


    ::-webkit-scrollbar {
        width: 0px;
    }

    ::-webkit-scrollbar-track {
        display: none;
    }

    .custom-calendar.vc-container {
        --day-border: 1px solid #b8c2cc;
        --day-border-highlight: 1px solid #b8c2cc;
        --day-width: 90px;
        --day-height: 90px;
        --weekday-bg: #f8fafc;
        --weekday-border: 1px solid #eaeaea;
        border-radius: 0;
        width: 100%;
    }

    .poster-event {
        background-color: rgba(255, 185, 185, .8);
        border: none;
        border-left: 3px solid rgba(230, 55, 55, .3);
        color: #c55656;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 98% !important;
        margin: 0 1%;
        padding: 10px;
    }

    .break-event {
        background-color: rgba(200, 248, 233, .8);
        border: none;
        border-left: 3px solid rgba(99, 186, 139, .4);
        color: #219671;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 98% !important;
        margin: 0 1%;
    }

    .other-event {
        background-color: rgba(255, 202, 154, .8);
        border: none;
        border-left: 3px solid rgba(250, 118, 36, .3);
        color: #b57335;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 98% !important;
        margin: 0 1%;
    }

    .plenary-event {
        background-color: rgba(239, 180, 241, 0.8);
        border: none;
        border-left: 3px solid rgba(250, 118, 36, .3);
        color: #b57335;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 98% !important;
        margin: 0 1%;
    }

    .parallel-event {
        background-color: rgba(167, 232, 167, 0.8);
        border: none;
        border-left: 3px solid rgba(250, 118, 36, .3);
        color: #b57335;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 98% !important;
        margin: 0 1%;
    }

    .special-event {
        background-color: rgba(154, 255, 243, 0.8);
        border: none;
        border-left: 3px solid rgba(250, 118, 36, .3);
        color: #b57335;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 98% !important;
        margin: 0 1%;
    }

    .vuecal__event-time {
        font-weight: bolder;
    }

    .day-split-header {
        cursor: pointer;
    }


</style>
