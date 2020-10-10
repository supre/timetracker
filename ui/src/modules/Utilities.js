import moment from "moment";

export const formatDateToAtom = (date) => {
  let m = moment(date);
  return m.format("YYYY-MM-DD");
};
