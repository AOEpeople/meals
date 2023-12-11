import { useParticipationsListData } from "@/api/getParticipationsByDay";
import { computed, reactive } from "vue";

interface ParticipantState {
  participants: any,
  filter: string,
  isLoading: boolean,
  error: string
}

export function filterParticipantsList(date: string){

  const { listData } = useParticipationsListData(date);
  const participations  = reactive<ParticipantState>({
    participants: listData,
    filter: '',
    isLoading: false,
    error: ''
  });

  function setFilter(filterStr: string) {
    participations.filter = filterStr;
  }


  const filteredParticipants = computed(() => {
    return participations.participants.filter(participant => participantsContainString(participant, participations.filter));
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
