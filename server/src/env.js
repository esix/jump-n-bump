// function gup(name) {
//   name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
//   var regexS = "[\\?&]" + name + "=([^&#]*)";
//   var regex = new RegExp(regexS);
//   var results = regex.exec(window.location.href);
//   if (results == null)
//     return "";
//   else
//     return results[1];
// }

const env = {
  JNB_MAX_PLAYERS: 4,
  MAX_OBJECTS: 200,
  animation_data: new Animation_Data(),
  level: {},
  pogostick: false,                                         // gup('pogostick') == '1',
  jetpack: false,                                           // gup('jetpack') == '1',
  bunnies_in_space: false,                                  // gup('space') == '1',
  flies_enabled: false,                                     // gup('lordoftheflies') == '1',
  blood_is_thicker_than_water: false,                       // gup('bloodisthickerthanwater') == '1',
  no_gore: false,                                           // gup('nogore') == '1'
};

// var players;

function rnd(max_value) {
  return Math.floor(Math.random() * max_value);
}
