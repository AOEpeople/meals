import { computed, reactive } from "vue";

interface ParticipantState {
  participants: string[],
  filter: string,
  isLoading: boolean,
  error: string
}

export interface Participant {
  name: string
}

export function filterParticipantsList(date: string){

  //const { listData } = usePrintableListData(date);
  const fruits: string[] = ['Apple', 'Orange', 'Banana'];


  function setFilter(filterStr: string) {
    listData.filter = filterStr;
    console.log(filteredParticipants);
  }

  const listData  = reactive<ParticipantState>({
    participants: fruits,
    filter: '',
    isLoading: false,
    error: ''
  });

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
    console.log(listData.participants);
    // let filteredList: any[] = [];
    // listData.forEach((fruits) => {}

    // )
    return listData.participants.filter(participant => participantsContainString(participant, listData.filter));
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
