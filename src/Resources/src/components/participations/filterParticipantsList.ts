import { usePrintableListData } from "@/api/getPrintableListData";
import { computed } from "vue";

export function filterParticipantsList(filterString: string, date: string){

  const { listData } = usePrintableListData(date);

  const filteredParticipants = computed(() => {
    let filteredList: any[] = [];
    return listData.data.filter(participant => (participantsContainString(participant, filterString);
  });

  function participantsContainString(participant: Participant) {
    return (
      participant.participantName.toLowerCase().includes(filterString.toLowerCase())
    );
}
  //    return {
  //     filteredList,
  //     setFilter
  //     filteredParticipants,
  //    };
  // });‚

}




