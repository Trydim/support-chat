<template>
  <div>
    <div @click="open">
      Помощь
    </div>

    <Dialog v-model:visible="visible" header="Поддержка"
            :style="{ width: '50rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" :position="position" :modal="true" :draggable="false">

      <div class="chat-messages">
        <div class="message-box-holder">
          <div class="message-box">Hello</div>
        </div>
        <div class="message-box-holder">
          <div class="message-sender">Mamun Khandaker</div>
          <div class="message-box message-partner">Hi.</div>
        </div>
        <div class="message-box-holder">
          <div class="message-box">How are you doing?</div>
        </div>
        <div class="message-box-holder">
          <div class="message-sender">Mamun Khandaker</div>
          <div class="message-box message-partner">I'm doing fine. How about you?</div>
        </div>
        <div class="message-box-holder">
          <div class="message-box">
            I am fine.
          </div>
        </div>
        <div class="message-box-holder">
          <div class="message-box">
            Do you know why I knocked you today?
          </div>
        </div>
        <div class="message-box-holder">
          <div class="message-box">
            There's something important I would like to share with you. Do you have some time?
          </div>
        </div>
        <div class="message-box-holder">
          <div class="message-sender">
            Mamun Khandaker
          </div>
          <div class="message-box message-partner">
            Yeah sure. Let's meet in the Einstein cafe this evening and discuss the matter.
          </div>
        </div>
        <div class="message-box-holder">
          <div class="message-sender">
            Mamun Khandaker
          </div>
          <div class="message-box message-partner">
            I thought of coming to your place and discuss about it but I had to finish my projects and I didn't have enough time to go out of the house.
          </div>
        </div>
        <div class="message-box-holder">
          <div class="message-sender">
            Mamun Khandaker
          </div>
          <div class="message-box message-partner">
            I thought of coming to your place and discuss about it but I had to finish my projects and I didn't have enough time to go out of the house.
          </div>
        </div>
        <div class="message-box-holder">
          <div class="message-sender">
            Mamun Khandaker
          </div>
          <div class="message-box message-partner">
            I thought of coming to your place and discuss about it but I had to finish my projects and I didn't have enough time to go out of the house.
          </div>
        </div>
        <div class="message-box-holder">
          <div class="message-sender">
            Mamun Khandaker
          </div>
          <div class="message-box message-partner">
            I thought of coming to your place and discuss about it but I had to finish my projects and I didn't have enough time to go out of the house.
          </div>
        </div>
        <div class="message-box-holder">
          <div class="message-sender">
            Mamun Khandaker
          </div>
          <div class="message-box message-partner">
            I thought of coming to your place and discuss about it but I had to finish my projects and I didn't have enough time to go out of the house.
          </div>
        </div>
      </div>

      <!--<div class="chatbox-holder">
        <div class="chatbox">
          <div class="chatbox-top">
            <div class="chat-partner-name">
              <span class="status online"></span>
              <a target="_blank" href="https://www.facebook.com/mfreak">Mamun Khandaker</a>
            </div>
            <div class="chatbox-icons">
              <a href="javascript:void(0);"><i class="fa fa-minus"></i></a>
              <a href="javascript:void(0);"><i class="fa fa-close"></i></a>
            </div>
          </div>

          <div class="chat-input-holder">
            <textarea class="chat-input"></textarea>
            <input type="submit" value="Send" class="message-send" />
          </div>
          <div class="attachment-panel">
            <a href="#" class="fa fa-thumbs-up"></a>
            <a href="#" class="fa fa-camera"></a>
            <a href="#" class="fa fa-video-camera"></a>
            <a href="#" class="fa fa-image"></a>
            <a href="#" class="fa fa-paperclip"></a>
            <a href="#" class="fa fa-link"></a>
            <a href="#" class="fa fa-trash-o"></a>
            <a href="#" class="fa fa-search"></a>
          </div>
        </div>

        <div class="chatbox ">
          <div class="chatbox-top">

            <div class="chat-group-name">
              <span class="status away"></span>
              Group name comes here
            </div>
            <div class="chatbox-icons">
              <label for="chkSettings"><i class="fa fa-gear"></i></label><input type="checkbox" id="chkSettings" />
              <div class="settings-popup">
                <ul>
                  <li><a href="#">Group members</a></li>
                  <li><a href="#">Add members</a></li>
                  <li><a href="#">Delete members</a></li>
                  <li><a href="#">Leave group</a></li>
                </ul>
              </div>
              <a href="javascript:void(0);"><i class="fa fa-minus"></i></a>
              <a href="javascript:void(0);"><i class="fa fa-close"></i></a>
            </div>
          </div>

          <div class="chat-messages">
            <div class="message-box-holder">
              <div class="message-box">
                What are you people doing?
              </div>
            </div>

            <div class="message-box-holder">
              <div class="message-sender">
                <a href="#">Ben Stiller</a>
              </div>
              <div class="message-box message-partner">
                Hey, nobody's here today. I'm at office alone.
                Hey, nobody's here today. I'm at office alone.
              </div>
            </div>

            <div class="message-box-holder">
              <div class="message-box">
                who else is online?
              </div>
            </div>

            <div class="message-box-holder">
              <div class="message-sender">
                <a href="#">Chris Jerrico</a>
              </div>
              <div class="message-box message-partner">
                I'm also online. How are you people?
              </div>
            </div>

            <div class="message-box-holder">
              <div class="message-box">
                I am fine.
              </div>
            </div>

            <div class="message-box-holder">
              <div class="message-sender">
                <a href="#">Rockey</a>
              </div>
              <div class="message-box message-partner">
                I'm also online. How are you people?
              </div>
            </div>

            <div class="message-box-holder">
              <div class="message-sender">
                <a href="#">Christina Farzana</a>
              </div>
              <div class="message-box message-partner">
                We are doing fine. I am in.
              </div>
            </div>
          </div>

          <div class="attachment-panel">
            <a href="#" class="fa fa-thumbs-up"></a>
            <a href="#" class="fa fa-camera"></a>
            <a href="#" class="fa fa-video-camera"></a>
            <a href="#" class="fa fa-image"></a>
            <a href="#" class="fa fa-paperclip"></a>
            <a href="#" class="fa fa-link"></a>
            <a href="#" class="fa fa-trash-o"></a>
            <a href="#" class="fa fa-search"></a>
          </div>
        </div>
      </div>-->

      <template #footer>
        <div class="chat-input-holder">
          <textarea class="chat-input" v-model="msg"></textarea>
          <input type="submit" class="message-send" value="Send" @click="send">
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script>

import {DEBUG, POSITION} from "./const";
import query from "./libs/query";

import Dialog from 'primevue/dialog';


export default {
  name: 'support-app',
  components: {Dialog},
  data: () => ({
    visible: false,
    position: 'topright',

    msg: 'test msg',
  }),
  computed: {},
  watch   : {},
  methods: {
    open() {
      this.visible = true;
    },

    send() {
      query.Post({
        data: {
          msg: this.msg
        }
      })
    }
  },
  mounted() {
    DEBUG && this.open();
  },
}
</script>
