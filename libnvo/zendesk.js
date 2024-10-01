const conversationBadge = document.querySelector('#conversation-badge')
const unreadIndicator = document.querySelector('#unread-indicator')


const populateUnreadIndicator = (count) => {
	if (!count) return resetUnreadIndicator()
  
  unreadIndicator.style.background = '#CC3333'
  unreadIndicator.innerHTML = count
  conversationBadge.setAttribute('class', 'tilt-animation');
  beep(100,);
}

const resetUnreadIndicator = () => {
  unreadIndicator.style.background = 'black'
  unreadIndicator.innerHTML = 0
  conversationBadge.setAttribute('class', '');
}

// unread Message on listener
zE('messenger:on', 'unreadMessages', (count) => {
  populateUnreadIndicator(count);
  
})

// on page load always close widget
zE('messenger', 'close');

conversationBadge.onclick = () => {
  // open widget
  zE('messenger', 'open');
  // reset unread indicator
  resetUnreadIndicator()
}




/**
 * Helper function to emit a beep sound in the browser using an Audio File.
 * 
 * @param {number} volume - The volume of the beep sound.
 * 
 * @returns {Promise} - A promise that resolves when the beep sound is finished.
 */
 function beep(volume){
  return new Promise((resolve, reject) => {
      volume = volume || 100;
    console.log(volume);
      try{
          // You're in charge of providing a valid AudioFile that can be reached by your web app
          let soundSource =  "libnvo/sound.wav";
          let sound = new Audio(soundSource);

          // Set volume
          sound.volume = volume / 100;

          sound.onended = () => {
              resolve();
          };

          sound.play();
          console.log("se reprodujo");
      }catch(error){
          reject(error);
      }
  });
}

