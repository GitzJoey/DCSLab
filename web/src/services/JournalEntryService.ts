import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { JournalEntry } from '../types/models/JournalEntry';
import { Resource } from '../types/resources/Resource';
import { ServiceResponse } from '../types/services/ServiceResponse';
import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import { JournalEntryReadAnyGetRequest } from '../types/services/journal-entry/JournalEntryRequest';
import { StatusCode } from '../types/enums/StatusCode';
import { client, useForm } from 'laravel-precognition-vue';

export default class JournalEntryService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public useJournalEntryCreateForm() {
    const url = route('api.post.journal_entry.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      source_type: null as string | null,
      source_id: null as number | null,
      reference_no: '',
      remarks: '',
      lines: [] as Array<{
        chart_of_account_id: string;
        debit: number;
        credit: number;
        remarks: string | null;
      }>,
    });
  }

  public useJournalEntryEditForm(ulid: string) {
    const url = route(
      'api.post.journal_entry.edit',
      {
        journal_entry: ulid,
      },
      true,
      this.ziggyRoute,
    );

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      reference_no: '',
      remarks: '',
      lines: [] as Array<{
        chart_of_account_id: string;
        debit: number;
        credit: number;
        remarks: string | null;
      }>,
    });
  }

  public async readAnyGet(
    args: JournalEntryReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<JournalEntry>> | null>> {
    const result: ServiceResponse<Resource<Array<JournalEntry>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed ? 1 : 0,
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
      if (args.source_type) queryParams.source_type = args.source_type;
      if (args.source_id) queryParams.source_id = args.source_id;

      const url = route(
        'api.get.journal_entry.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Array<JournalEntry>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<JournalEntry | null>> {
    const result: ServiceResponse<JournalEntry | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.get.journal_entry.read',
        {
          journal_entry: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<JournalEntry>> = await axios.get(url);

      if (response.status == StatusCode.OK) {
        result.success = true;
        result.data = response.data.data;
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

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.post.journal_entry.delete',
        {
          journal_entry: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<boolean | null> = await axios.post(url);

      if (response.status == StatusCode.OK) {
        result.success = true;
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
