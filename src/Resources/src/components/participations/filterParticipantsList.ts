import { useParticipationsListData } from "@/api/getParticipationsByDay";
import { computed } from "vue";

interface ParticipantState {
  participants: any,
  filter: string,
  isLoading: boolean,
  error: string
}

export interface Participant {
  name: string
}

export function filterParticipantsList(date: string){

  const { listData } = useParticipationsListData(date);
  //const fruits: string[] = ['Apple', 'Orange', 'Banana'];


  function setFilter(filterStr: string) {
    //listData.filter = filterStr;
    console.log(filteredParticipants);
  }


  // const filteredNames = computed(() => {
  //   const filteredList: any[] = [];

  //   listData.participants.forEach((item) => {
  //     if (
  //       item.startsWith(filterString)
  //     ) {
  //       filteredList.push(item);
  //     }
  //   });

  //   return filteredList;
  // });

  const filteredParticipants = computed(() => {
    //console.log(listData.participants);
    // let filteredList: any[] = [];
    // listData.forEach((fruits) => {}

    // )
    return listData.value.filter(participant => participantsContainString(participant, ''));
  });


  function participantsContainString(participant: string, filterInput: string) {
    console.log(filterInput);
    return (
      participant.toLowerCase().includes(filterInput.toLowerCase())
    );
  }

  return {
      filteredParticipants,
      setFilter
  };

}
