import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, type Config } from 'ziggy-js';
import type { JournalEntryItem } from '../types/models/JournalEntry';
import type { Resource } from '../types/resources/Resource';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import type { JournalEntryItemReadAnyGetRequest } from '../types/services/journal-entry-item/JournalEntryItemRequest';
import { StatusCode } from '../types/enums/StatusCode';

export default class JournalEntryItemService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyGet(
    args: JournalEntryItemReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<JournalEntryItem>> | null>> {
    const result: ServiceResponse<Resource<Array<JournalEntryItem>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {
        company_id: args.company_id,
        refresh: args.refresh ? 1 : 0,
        get: {
          limit: args.limit,
        },
      };

      if (args.branch_id) queryParams.branch_id = args.branch_id;
      if (args.search) queryParams.search = args.search;
      if (args.start_date) queryParams.start_date = args.start_date;
      if (args.end_date) queryParams.end_date = args.end_date;
      if (args.journal_entry_id) queryParams.journal_entry_id = args.journal_entry_id;
      if (args.chart_of_account_id) queryParams.chart_of_account_id = args.chart_of_account_id;
      if (args.include_id) queryParams.include_id = args.include_id;

      const url = route('api.get.journal_entry_item.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<JournalEntryItem>>> = await axios.get(url);

      if (response.status == StatusCode.OK) {
        result.success = true;
        result.data = response.data;
      }

      return result;
    } catch (e: unknown) {
      if (e instanceof Error && e.message.includes('Ziggy error')) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
      } else {
        return result;
      }
    }
  }
}
